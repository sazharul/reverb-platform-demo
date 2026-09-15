<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Library\SslCommerz\SslCommerzNotification;
use App\Models\Payment;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // Checkout page
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Show checkout / confirmation page before redirecting to gateway.
     */
    public function checkout(SubscriptionPlan $plan)
    {
        if (!$plan->is_active || $plan->isFree()) {
            return redirect()->route('user.plans.index')
                ->with('error', 'This plan cannot be purchased online.');
        }

        if (! config('app.demo_mode') && empty(config('sslcommerz.apiCredentials.store_id'))) {
            return redirect()->route('user.plans.index')
                ->with('error', 'Payment gateway is not configured. Please contact the administrator.');
        }

        $user        = auth()->user();
        $currentPlan = $user->currentPlan();

        return view('dashboard.payments.checkout', compact('plan', 'currentPlan'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Initiate – redirect user to SSLCommerz gateway
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Initiate SSLCommerz payment session and redirect user to the gateway page.
     * Uses SslCommerzNotification::makePayment() exactly like the official sample.
     */
    public function initiate(Request $request, SubscriptionPlan $plan)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
        ]);

        if (!$plan->is_active || $plan->isFree()) {
            return redirect()->route('user.plans.index')
                ->with('error', 'This plan cannot be purchased online.');
        }

        if (config('app.demo_mode')) {
            return $this->activateDemoPlan($plan, $request->input('phone'));
        }

        if (empty(config('sslcommerz.apiCredentials.store_id'))) {
            return redirect()->route('user.plans.index')
                ->with('error', 'Payment gateway is not configured.');
        }

        $user          = auth()->user();
        $transactionId = 'PW-' . strtoupper(Str::random(16)) . '-' . time();

        // Store payment as Pending before going to gateway
        $payment = Payment::create([
            'user_id'              => $user->id,
            'subscription_plan_id' => $plan->id,
            'transaction_id'       => $transactionId,
            'amount'               => $plan->price,
            'currency'             => config('sslcommerz.currency', 'BDT'),
            'status'               => 'pending',
        ]);

        $post_data = [
            'total_amount'     => $plan->price,
            'currency'         => config('sslcommerz.currency', 'BDT'),
            'tran_id'          => $transactionId,

            // Product info
            'product_name'     => $plan->name . ' Plan (' . $plan->billing_cycle . ')',
            'product_category' => 'Subscription',
            'product_profile'  => 'non-physical-goods',

            // Customer info
            'cus_name'         => $user->name,
            'cus_email'        => $user->email,
            'cus_add1'         => 'N/A',
            'cus_city'         => 'Dhaka',
            'cus_country'      => 'Bangladesh',
            'cus_phone'        => $request->input('phone'),

            // Shipping (not applicable)
            'shipping_method'  => 'NO',
            'ship_name'        => $user->name,
            'ship_add1'        => 'N/A',
            'ship_city'        => 'Dhaka',
            'ship_country'     => 'Bangladesh',

            // Extra reference values (accessible in callbacks)
            'value_a'          => $payment->id,
            'value_b'          => $user->id,
            'value_c'          => $plan->id,
            'value_d'          => $plan->billing_cycle,
        ];

        $sslc            = new SslCommerzNotification();
        $payment_options = $sslc->makePayment($post_data, 'checkout', 'json');

        Log::info('SSLCOMMERZ initiate response', [
            'tran_id'  => $transactionId,
            'response' => $payment_options,
        ]);

        $decoded = json_decode($payment_options, true);

        // Success: 'status' => 'success' (sandbox) or 'SUCCESS' (live)
        if (!empty($decoded['data']) && isset($decoded['status']) && strtolower($decoded['status']) === 'success') {
            $payment->update(['session_id' => null, 'status' => 'pending']);
            return redirect()->away($decoded['data']);
        }

        // Gateway rejected
        $reason = $decoded['message'] ?? 'Unknown error from payment gateway.';
        $payment->update(['status' => 'failed', 'notes' => $reason]);

        return redirect()->route('user.plans.index')
            ->with('error', 'Unable to connect to payment gateway: ' . $reason);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Callbacks
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * SSLCommerz success callback (browser redirect – GET or POST).
     *
     * Mirror of the reference SslCommerzPaymentController::success():
     *   - If status == 'Pending'  → validate via orderValidate()
     *   - If status == 'Processing' / 'Complete' → already handled (IPN arrived first)
     *
     * Extra: sandbox always returns status=PROCESSING from the gateway,
     * so we also accept that as paid even when orderValidate() can't confirm it.
     */
    public function success(Request $request)
    {
        $tran_id  = $request->input('tran_id');
        $amount   = $request->input('amount');
        $currency = $request->input('currency', config('sslcommerz.currency', 'BDT'));
        $gwStatus = strtoupper((string) $request->input('status', ''));

        Log::info('SSLCOMMERZ success callback', [
            'tran_id' => $tran_id,
            'status'  => $gwStatus,
            'all'     => $request->all(),
        ]);

        if (!$tran_id) {
            return redirect()->route('user.plans.index')
                ->with('error', 'Invalid payment callback received.');
        }

        $payment = Payment::where('transaction_id', $tran_id)->first();

        if (!$payment) {
            return redirect()->route('user.plans.index')
                ->with('error', 'Payment record not found.');
        }

        // Already completed (IPN arrived first – same as reference "Processing/Complete" branch)
        if ($payment->isCompleted()) {
            return redirect()->route('user.payments.receipt', $payment)
                ->with('success', 'Payment confirmed! Your plan is active.');
        }

        // Status is Pending – validate with SSLCommerz (same as reference)
        if ($payment->status === 'pending') {
            $sslc       = new SslCommerzNotification();
            $validation = $sslc->orderValidate(
                $request->all(),
                $tran_id,
                $amount ?? $payment->amount,
                $currency
            );

            // orderValidate returns true for VALID/VALIDATED.
            // For sandbox PROCESSING we also accept the gateway status directly.
            if ($validation === true || $gwStatus === 'PROCESSING') {
                $this->completePayment($payment, $request->all());

                return redirect()->route('user.payments.receipt', $payment)
                    ->with('success', 'Payment successful! Your plan has been activated.');
            }

            // Validation rejected
            $payment->update([
                'status'           => 'failed',
                'notes'            => 'orderValidate returned false. GW status: ' . $gwStatus,
                'gateway_response' => $request->all(),
            ]);

            return redirect()->route('user.plans.index')
                ->with('error', 'Payment could not be verified. Contact support if money was deducted.');
        }

        // Any other unexpected local status
        return redirect()->route('user.plans.index')
            ->with('error', 'Unexpected payment state (' . $payment->status . '). Contact support.');
    }

    /**
     * SSLCommerz fail callback (GET or POST).
     */
    public function fail(Request $request)
    {
        $tran_id = $request->input('tran_id');

        Log::info('SSLCOMMERZ fail callback', $request->all());

        $payment = Payment::where('transaction_id', $tran_id)->first();
        if ($payment && !$payment->isCompleted()) {
            $payment->update([
                'status'           => 'failed',
                'gateway_response' => $request->all(),
            ]);
        }

        return redirect()->route('user.plans.index')
            ->with('error', 'Payment failed. Please try again or contact support.');
    }

    /**
     * SSLCommerz cancel callback (GET or POST).
     */
    public function cancel(Request $request)
    {
        $tran_id = $request->input('tran_id');

        Log::info('SSLCOMMERZ cancel callback', $request->all());

        $payment = Payment::where('transaction_id', $tran_id)->first();
        if ($payment && !$payment->isCompleted()) {
            $payment->update([
                'status'           => 'cancelled',
                'gateway_response' => $request->all(),
            ]);
        }

        return redirect()->route('user.plans.index')
            ->with('error', 'Payment was cancelled.');
    }

    /**
     * SSLCommerz IPN – server-to-server (no browser session, no auth middleware).
     * Mirror of the reference SslCommerzPaymentController::ipn().
     */
    public function ipn(Request $request)
    {
        Log::info('SSLCOMMERZ IPN received', $request->all());

        $tran_id = $request->input('tran_id');

        if (!$tran_id) {
            echo "Invalid Data";
            return response('INVALID DATA', 400);
        }

        $payment = Payment::where('transaction_id', $tran_id)->first();

        if (!$payment) {
            Log::warning('SSLCOMMERZ IPN: payment not found', ['tran_id' => $tran_id]);
            echo "Invalid Transaction";
            return response('NOT FOUND', 404);
        }

        // Already completed (IPN fired twice / race condition)
        if ($payment->isCompleted()) {
            echo "Transaction is already successfully Completed";
            return response('ALREADY COMPLETED', 200);
        }

        if ($payment->status === 'pending') {
            $sslc       = new SslCommerzNotification();
            $validation = $sslc->orderValidate(
                $request->all(),
                $tran_id,
                $payment->amount,
                $payment->currency
            );

            if ($validation === true) {
                $this->completePayment($payment, $request->all());
                echo "Transaction is successfully Completed";
                return response('COMPLETED', 200);
            }
        } else if ($payment->isCompleted()) {
            echo "Transaction is already successfully Completed";
            return response('ALREADY COMPLETED', 200);
        }

        $payment->update([
            'status'           => 'failed',
            'notes'            => 'IPN orderValidate returned false',
            'gateway_response' => $request->all(),
        ]);

        echo "Invalid Transaction";
        return response('FAILED', 200);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Other pages
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Show payment receipt.
     */
    public function receipt(Payment $payment)
    {
        if ($payment->user_id !== auth()->id()) {
            abort(403);
        }

        $payment->load('plan');
        return view('dashboard.payments.receipt', compact('payment'));
    }

    /**
     * Payment history.
     */
    public function history()
    {
        $payments = Payment::where('user_id', auth()->id())
            ->with('plan')
            ->latest()
            ->paginate(20);

        return view('dashboard.payments.history', compact('payments'));
    }

    /**
     * Subscribe to a free plan (no payment needed).
     */
    public function subscribeFree(SubscriptionPlan $plan)
    {
        if (!$plan->is_active || !$plan->isFree()) {
            return redirect()->route('user.plans.index')
                ->with('error', 'This plan is not free.');
        }

        $user = auth()->user();

        UserSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->update(['status' => 'cancelled']);

        UserSubscription::create([
            'user_id'              => $user->id,
            'subscription_plan_id' => $plan->id,
            'status'               => 'active',
            'starts_at'            => now(),
            'expires_at'           => null,
            'notes'                => 'Self-subscribed to free plan',
        ]);

        return redirect()->route('user.plans.index')
            ->with('success', 'You are now on the ' . $plan->name . ' plan.');
    }

    /**
     * DEMO_MODE: activate a paid plan without SSLCommerz.
     */
    private function activateDemoPlan(SubscriptionPlan $plan, ?string $phone = null)
    {
        $user = auth()->user();
        $transactionId = 'PW-DEMO-' . strtoupper(Str::random(12));

        $payment = Payment::create([
            'user_id'              => $user->id,
            'subscription_plan_id' => $plan->id,
            'transaction_id'       => $transactionId,
            'amount'               => $plan->price,
            'currency'             => config('sslcommerz.currency', 'BDT'),
            'status'               => 'pending',
            'notes'                => 'DEMO_MODE instant activation',
        ]);

        $this->completePayment($payment, [
            'val_id'       => 'demo-val-id',
            'card_type'    => 'demo',
            'card_brand'   => 'demo',
            'bank_tran_id' => $transactionId,
            'tran_date'    => now()->toDateTimeString(),
            'cus_phone'    => $phone,
        ]);

        return redirect()->route('user.plans.index')
            ->with('success', 'Demo mode: ' . $plan->name . ' plan activated instantly.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Mark payment as completed and activate the user's subscription.
     * Runs inside a DB transaction.
     */
    private function completePayment(Payment $payment, array $data): void
    {
        DB::transaction(function () use ($payment, $data) {
            $payment->update([
                'status'           => 'completed',
                'val_id'           => $data['val_id'] ?? null,
                'payment_method'   => $data['card_type'] ?? null,
                'card_type'        => $data['card_type'] ?? null,
                'card_brand'       => $data['card_brand'] ?? null,
                'bank_tran_id'     => $data['bank_tran_id'] ?? null,
                'tran_date'        => $data['tran_date'] ?? null,
                'gateway_response' => $data,
                'paid_at'          => now(),
            ]);

            // Deactivate any current active subscription
            UserSubscription::where('user_id', $payment->user_id)
                ->where('status', 'active')
                ->update(['status' => 'cancelled']);

            $plan      = $payment->plan;
            $expiresAt = match ($plan->billing_cycle) {
                'monthly'  => now()->addMonth(),
                'yearly'   => now()->addYear(),
                'lifetime' => null,
                default    => now()->addMonth(),
            };

            UserSubscription::create([
                'user_id'              => $payment->user_id,
                'subscription_plan_id' => $payment->subscription_plan_id,
                'status'               => 'active',
                'starts_at'            => now(),
                'expires_at'           => $expiresAt,
                'notes'                => 'Purchased via SSLCOMMERZ. Tran: ' . $payment->transaction_id
                    . ' | Status: ' . ($data['status'] ?? 'N/A'),
            ]);
        });
    }
}


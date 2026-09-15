<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SslCommerzService
{
    protected string $storeId;
    protected string $storePassword;
    protected string $initUrl;
    protected string $validationUrl;
    protected bool   $sandbox;
    protected bool   $connectFromLocalhost;

    public function __construct()
    {
        $this->storeId              = (string) config('sslcommerz.store_id', '');
        $this->storePassword        = (string) config('sslcommerz.store_password', '');
        $this->sandbox              = (bool)   config('sslcommerz.sandbox', true);
        $this->connectFromLocalhost = (bool)   config('sslcommerz.connect_from_localhost', true);
        $this->initUrl              = (string) config('sslcommerz.init_url', '');
        $this->validationUrl        = (string) config('sslcommerz.validation_url', '');
    }

    /**
     * Initiate a payment session with SSLCOMMERZ.
     * Returns gateway response (with GatewayPageURL) on success, null on failure.
     */
    public function initiatePayment(array $data): ?array
    {
        $postData = array_merge([
            'store_id'         => $this->storeId,
            'store_passwd'     => $this->storePassword,
            'currency'         => config('sslcommerz.currency', 'BDT'),
            'shipping_method'  => 'NO',
            'product_category' => 'Subscription',
            'product_profile'  => 'non-physical-goods',
            'cus_add1'         => 'N/A',
            'cus_city'         => 'N/A',
            'cus_country'      => 'Bangladesh',
            'cus_postcode'     => '0000',
        ], $data);

        Log::info('SSLCOMMERZ initiate request', [
            'url'     => $this->initUrl,
            'sandbox' => $this->sandbox,
            'tran_id' => $data['tran_id'] ?? null,
        ]);

        try {
            $http = Http::asForm();

            // In sandbox / localhost skip SSL certificate verification
            if ($this->connectFromLocalhost) {
                $http = $http->withoutVerifying();
            }

            $response = $http->post($this->initUrl, $postData);
            $result   = $response->json();

            Log::info('SSLCOMMERZ initiate response', [
                'tran_id' => $data['tran_id'] ?? null,
                'status'  => $result['status'] ?? 'N/A',
            ]);

            if (isset($result['status']) && $result['status'] === 'SUCCESS') {
                return $result;
            }

            Log::error('SSLCOMMERZ initiation failed', [
                'response' => $result,
                'tran_id'  => $data['tran_id'] ?? null,
            ]);

            return null;
        } catch (\Throwable $e) {
            Log::error('SSLCOMMERZ initiation exception', [
                'message' => $e->getMessage(),
                'tran_id' => $data['tran_id'] ?? null,
            ]);

            return null;
        }
    }

    /**
     * Validate a payment by val_id after IPN/success callback.
     * VALID / VALIDATED = live success.
     * PROCESSING        = sandbox / mobile-banking in-progress – treat as success.
     */
    public function validatePayment(string $valId): ?array
    {
        if (empty($this->validationUrl)) {
            return null;
        }

        try {
            $http = Http::timeout(20);

            if ($this->connectFromLocalhost) {
                $http = $http->withoutVerifying();
            }

            $response = $http->get($this->validationUrl, [
                'val_id'       => $valId,
                'store_id'     => $this->storeId,
                'store_passwd' => $this->storePassword,
                'format'       => 'json',
            ]);

            $result = $response->json();

            // Statuses that mean "payment accepted"
            $acceptedStatuses = ['VALID', 'VALIDATED', 'PROCESSING'];

            if (isset($result['status']) && in_array(strtoupper($result['status']), $acceptedStatuses)) {
                // Normalise status so downstream code always sees VALID
                if (strtoupper($result['status']) === 'PROCESSING') {
                    $result['status'] = 'VALID';
                }

                return $result;
            }

            Log::warning('SSLCOMMERZ validation not acceptable', [
                'response' => $result,
                'val_id'   => $valId,
            ]);

            return null;
        } catch (\Throwable $e) {
            Log::error('SSLCOMMERZ validation exception', [
                'message' => $e->getMessage(),
                'val_id'  => $valId,
            ]);

            return null;
        }
    }

    /**
     * Verify IPN hash to ensure the callback is genuine.
     */
    public function verifyIpnHash(array $data): bool
    {
        if (!isset($data['verify_sign'], $data['verify_key'])) {
            return false;
        }

        $verifySign = $data['verify_sign'];
        $verifyKey  = $data['verify_key'];

        $keys = explode(',', $verifyKey);
        sort($keys);

        $hashString = '';
        foreach ($keys as $key) {
            $hashString .= $key . '=' . ($data[$key] ?? '') . '&';
        }

        return md5($hashString) === $verifySign;
    }

    /**
     * Check if the service is properly configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->storeId) && !empty($this->storePassword) && !empty($this->initUrl);
    }
}


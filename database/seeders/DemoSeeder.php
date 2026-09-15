<?php

namespace Database\Seeders;

use App\Models\App;
use App\Models\Channel;
use App\Models\EventLog;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public const DEMO_APP_KEY = 'key123456789012345678901234567890';

    public const DEMO_APP_SECRET = 'demosecretdemosecretdemosecretdemosecretdemosecretdemosecretdemo12';

    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'demo@pulsewire.demo'],
            [
                'name'      => 'Demo User',
                'password'  => Hash::make('DemoUser123!'),
                'user_type' => 'user',
                'is_active' => true,
            ]
        );

        $freePlan = SubscriptionPlan::where('slug', 'free')->first();

        if ($freePlan) {
            UserSubscription::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'subscription_plan_id' => $freePlan->id,
                    'status' => 'active',
                ],
                [
                    'starts_at'  => now(),
                    'expires_at' => null,
                    'notes'      => 'Demo seeded subscription',
                ]
            );
        }

        $app = App::firstOrCreate(
            ['app_key' => self::DEMO_APP_KEY],
            [
                'user_id'         => $user->id,
                'name'            => 'Demo Store App',
                'description'     => 'Pre-seeded demo application for API examples.',
                'app_id'          => 'demoapp123456',
                'app_secret'      => self::DEMO_APP_SECRET,
                'is_active'       => true,
                'max_connections' => 200,
                'allowed_origins' => ['*'],
            ]
        );

        foreach (['orders', 'notifications'] as $channelName) {
            Channel::firstOrCreate(
                [
                    'app_id' => $app->id,
                    'name'   => $channelName,
                ],
                [
                    'type'      => 'public',
                    'is_active' => true,
                ]
            );
        }

        if ($app->eventLogs()->count() === 0) {
            EventLog::create([
                'app_id'    => $app->id,
                'channel'   => 'orders',
                'event_name' => 'product.added_to_cart',
                'payload'   => ['product_id' => 10, 'qty' => 1],
                'source'    => 'api',
                'status'    => 'delivered',
                'delivered_at' => now()->subMinutes(5),
            ]);

            EventLog::create([
                'app_id'    => $app->id,
                'channel'   => 'notifications',
                'event_name' => 'order.shipped',
                'payload'   => ['order_id' => 'PW-10492'],
                'source'    => 'api',
                'status'    => 'delivered',
                'delivered_at' => now()->subMinutes(2),
            ]);
        }
    }
}

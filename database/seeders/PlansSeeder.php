<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class PlansSeeder extends Seeder
{
    public function run(): void
    {
        SubscriptionPlan::firstOrCreate(
            ['slug' => 'free'],
            [
                'name'                    => 'Free',
                'description'             => 'Get started with basic real-time messaging. Perfect for hobby projects and prototyping.',
                'price'                   => 0,
                'billing_cycle'           => 'monthly',
                'max_apps'                => 3,
                'max_connections_per_app' => 50,
                'daily_message_limit'     => 5000,
                'max_channels_per_app'    => 20,
                'webhook_allowed'         => false,
                'is_active'               => true,
                'sort_order'              => 1,
            ]
        );

        SubscriptionPlan::firstOrCreate(
            ['slug' => 'starter'],
            [
                'name'                    => 'Starter',
                'description'             => 'For growing applications that need more capacity and features.',
                'price'                   => 29.00,
                'billing_cycle'           => 'monthly',
                'max_apps'                => 10,
                'max_connections_per_app' => 500,
                'daily_message_limit'     => 100000,
                'max_channels_per_app'    => 100,
                'webhook_allowed'         => true,
                'is_active'               => true,
                'sort_order'              => 2,
            ]
        );

        SubscriptionPlan::firstOrCreate(
            ['slug' => 'pro'],
            [
                'name'                    => 'Pro',
                'description'             => 'For production workloads. Unlimited messaging with premium support.',
                'price'                   => 99.00,
                'billing_cycle'           => 'monthly',
                'max_apps'                => 50,
                'max_connections_per_app' => 5000,
                'daily_message_limit'     => 1000000,
                'max_channels_per_app'    => 500,
                'webhook_allowed'         => true,
                'is_active'               => true,
                'sort_order'              => 3,
            ]
        );

        SubscriptionPlan::firstOrCreate(
            ['slug' => 'enterprise'],
            [
                'name'                    => 'Enterprise',
                'description'             => 'Unlimited everything. Dedicated infrastructure and priority support.',
                'price'                   => 499.00,
                'billing_cycle'           => 'monthly',
                'max_apps'                => 999999,
                'max_connections_per_app' => 999999,
                'daily_message_limit'     => 999999,
                'max_channels_per_app'    => 999999,
                'allow_any_channel'       => true,
                'webhook_allowed'         => true,
                'is_active'               => true,
                'sort_order'              => 4,
            ]
        );
    }
}


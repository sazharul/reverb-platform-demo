<?php

use App\Models\App;
use App\Models\EventLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('event trigger rejects invalid signature', function () {
    $user = User::factory()->create();

    $app = App::create([
        'user_id' => $user->id,
        'name' => 'Realtime App',
        'app_id' => 'app123456789',
        'app_key' => 'key123456789012345678901234567890',
        'app_secret' => str_repeat('s', 64),
        'is_active' => true,
        'max_connections' => 200,
    ]);

    $payload = [
        'channel' => 'orders',
        'event_name' => 'product.added_to_cart',
        'data' => ['product_id' => 10],
    ];

    $response = $this
        ->withHeaders([
            'X-App-Key' => $app->app_key,
            'X-Signature' => 'invalid-signature',
        ])
        ->postJson('/api/v1/events', $payload);

    $response->assertStatus(401)
        ->assertJson(['message' => 'Invalid signature.']);

    expect(EventLog::count())->toBe(0);
});

test('event trigger stores and marks event as delivered when signature is valid', function () {
    $user = User::factory()->create();

    $app = App::create([
        'user_id' => $user->id,
        'name' => 'Realtime App',
        'app_id' => 'app987654321',
        'app_key' => 'key987654321098765432109876543210',
        'app_secret' => str_repeat('a', 64),
        'is_active' => true,
        'max_connections' => 200,
    ]);

    $payload = [
        'channel' => 'orders',
        'event_name' => 'product.added_to_cart',
        'data' => ['product_id' => 10, 'qty' => 1],
    ];

    $signature = hash_hmac('sha256', json_encode($payload), $app->revealSecret());

    $pusherMock = \Mockery::mock('overload:Pusher\\Pusher');
    $pusherMock->shouldReceive('trigger')->once()->andReturn(true);

    $response = $this
        ->withHeaders([
            'X-App-Key' => $app->app_key,
            'X-Signature' => $signature,
        ])
        ->postJson('/api/v1/events', $payload);

    $response->assertStatus(202)
        ->assertJson(['message' => 'Event delivered.']);

    $event = EventLog::query()->first();

    expect($event)->not->toBeNull();
    expect($event->status)->toBe('delivered');
    expect($event->delivered_at)->not->toBeNull();
});


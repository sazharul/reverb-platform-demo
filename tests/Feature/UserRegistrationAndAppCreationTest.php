<?php

use App\Models\App;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a user can register and create an app with credentials', function () {
    $registerResponse = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'user@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $registerResponse->assertRedirect(route('dashboard'));

    $createResponse = $this->post(route('user.apps.store'), [
        'name' => 'Enorsia',
        'description' => 'Realtime events app',
        'default_channel_type' => 'private',
        'allowed_origins' => "https://enorsia.com\nhttps://app.enorsia.com",
        'max_connections' => 500,
    ]);

    $app = App::query()->first();

    expect($app)->not->toBeNull();
    expect(strlen($app->app_id))->toBe(12);
    expect(strlen($app->app_key))->toBe(32);
    expect(strlen($app->revealSecret()))->toBe(64);
    expect($app->default_channel_type)->toBe('private');

    $createResponse->assertRedirect(route('user.apps.show', $app));
    $this->assertAuthenticated();
});

<?php

namespace App\Reverb;

use App\Models\App as ReverbTenantApp;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Laravel\Reverb\Application;
use Laravel\Reverb\Contracts\ApplicationProvider;
use Laravel\Reverb\Exceptions\InvalidApplication;

class ReverbAppManager implements ApplicationProvider
{
    private const CACHE_TTL_SECONDS = 300;

    public function all(): Collection
    {
        return ReverbTenantApp::query()
            ->where('is_active', true)
            ->get()
            ->map(fn (ReverbTenantApp $app) => $this->toReverbApplication($app));
    }

    public function findById(string $id): Application
    {
        $cacheKey = $this->cacheKeyById($id);

        $app = Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($id) {
            return ReverbTenantApp::query()
                ->where('app_id', $id)
                ->where('is_active', true)
                ->first();
        });

        if (! $app instanceof ReverbTenantApp) {
            throw new InvalidApplication;
        }

        return $this->toReverbApplication($app);
    }

    public function findByKey(string $key): Application
    {
        $cacheKey = $this->cacheKeyByKey($key);

        $app = Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($key) {
            return ReverbTenantApp::query()
                ->where('app_key', $key)
                ->where('is_active', true)
                ->first();
        });

        if (! $app instanceof ReverbTenantApp) {
            throw new InvalidApplication;
        }

        return $this->toReverbApplication($app);
    }

    public static function clearCache(ReverbTenantApp $app): void
    {
        Cache::forget(self::cacheKeyById($app->app_id));
        Cache::forget(self::cacheKeyByKey($app->app_key));
    }

    private function toReverbApplication(ReverbTenantApp $app): Application
    {
        $defaultAppConfig = (array) config('reverb.apps.apps.0', []);
        $defaultOptions = (array) ($defaultAppConfig['options'] ?? []);
        $defaultRateLimiting = (array) ($defaultAppConfig['rate_limiting'] ?? []);

        return new Application(
            id: $app->app_id,
            key: $app->app_key,
            secret: $app->revealSecret(),
            pingInterval: (int) ($defaultAppConfig['ping_interval'] ?? 60),
            activityTimeout: (int) ($defaultAppConfig['activity_timeout'] ?? 30),
            allowedOrigins: $app->allowed_origins ?: ['*'],
            maxMessageSize: (int) ($defaultAppConfig['max_message_size'] ?? 10_000),
            maxConnections: $app->max_connections,
            acceptClientEventsFrom: (string) ($defaultAppConfig['accept_client_events_from'] ?? 'members'),
            rateLimiting: [
                'enabled' => (bool) ($defaultRateLimiting['enabled'] ?? false),
                'max_attempts' => (int) ($defaultRateLimiting['max_attempts'] ?? 60),
                'decay_seconds' => (int) ($defaultRateLimiting['decay_seconds'] ?? 60),
                'terminate_on_limit' => (bool) ($defaultRateLimiting['terminate_on_limit'] ?? false),
            ],
            options: [
                'host' => $defaultOptions['host'] ?? config('reverb.servers.reverb.hostname'),
                'port' => (int) ($defaultOptions['port'] ?? 443),
                'scheme' => $defaultOptions['scheme'] ?? 'https',
                'useTLS' => (bool) ($defaultOptions['useTLS'] ?? true),
            ],
        );
    }

    private static function cacheKeyById(string $id): string
    {
        return 'reverb:app:id:'.$id;
    }

    private static function cacheKeyByKey(string $key): string
    {
        return 'reverb:app:key:'.$key;
    }
}


<?php

namespace App\Providers;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Laravel\Reverb\ApplicationManager;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ── Share site name from SystemSetting to all views ──────────────────
        View::composer('*', function ($view) {
            $view->with('siteName', SystemSetting::get('site_name', config('app.name')));
        });

        // ── Reverb: register custom database app provider ────────────────────
        $this->app->afterResolving(ApplicationManager::class, function (ApplicationManager $manager): void {
            $manager->extend('database', function ($app) {
                return $app->make(\App\Reverb\ReverbAppManager::class);
            });
        });

        // ── Pulse: authorize the /pulse dashboard ────────────────────────────
        // Only superadmins and admins can view the Pulse dashboard.
        Gate::define('viewPulse', function ($user) {
            return $user && ($user->isSuperAdmin() || $user->isAdminType());
        });

        // ── Pulse: register custom Reverb cards ──────────────────────────────
        Livewire::component('reverb-messages', \App\Livewire\Pulse\ReverbMessages::class);
        Livewire::component('reverb-channels', \App\Livewire\Pulse\ReverbChannels::class);

        // ── Dashboard: live event log table ──────────────────────────────────
        Livewire::component('dashboard.event-log-table', \App\Livewire\Dashboard\EventLogTable::class);
    }
}

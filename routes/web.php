<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Dashboard\UserDashboardController;
use App\Http\Controllers\Admin\AdminDashboardController;

use App\Http\Controllers\Dashboard\AppController as DashboardAppController;
use App\Http\Controllers\Dashboard\ChannelController;
use App\Http\Controllers\Dashboard\EventLogController;
use App\Http\Controllers\Dashboard\StatsController;
use App\Http\Controllers\Dashboard\ReverbChannelAuthController;
use App\Http\Controllers\Dashboard\SettingsController as DashboardSettingsController;
use App\Http\Controllers\Dashboard\PlanController as DashboardPlanController;
use App\Http\Controllers\Dashboard\PaymentController;
use App\Http\Controllers\Dashboard\DocumentationController;

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AppController as AdminAppController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\PlanController as AdminPlanController;

// ── Public ────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',[AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::get('/', fn() => redirect()->route('login'));

// ── SSLCOMMERZ IPN (server-to-server, no auth) ───────────────────────
Route::post('/payments/ipn', [PaymentController::class, 'ipn'])->name('payments.ipn');

// ── User panel ────────────────────────────────────────────────────────
Route::middleware(['auth', 'active.user'])
    ->group(function () {
        Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');

        Route::prefix('user')->name('user.')->group(function () {
            Route::resource('apps',     DashboardAppController::class);
            Route::resource('channels', ChannelController::class);

            // Event Logs
            Route::get('events',        [EventLogController::class, 'index'])->name('events.index');
            Route::get('events/{event}',[EventLogController::class, 'show'])->name('events.show');

            // Stats
            Route::get('stats',         [StatsController::class, 'index'])->name('stats');

            // Settings
            Route::get('settings',             [DashboardSettingsController::class, 'index'])->name('settings');
            Route::post('settings/profile',    [DashboardSettingsController::class, 'updateProfile'])->name('settings.profile');
            Route::post('settings/password',   [DashboardSettingsController::class, 'updatePassword'])->name('settings.password');
            Route::post('settings/tokens',     [DashboardSettingsController::class, 'createToken'])->name('settings.tokens.store');
            Route::delete('settings/tokens/{tokenId}', [DashboardSettingsController::class, 'revokeToken'])->name('settings.tokens.destroy');

            // Plans
            Route::get('plans',         [DashboardPlanController::class, 'index'])->name('plans.index');

            // Payments / Subscription Purchase
            Route::prefix('payments')->name('payments.')->group(function () {
                Route::get('{plan}/checkout',        [PaymentController::class, 'checkout'])->name('checkout');
                Route::post('{plan}/initiate',       [PaymentController::class, 'initiate'])->name('initiate');

                // SSLCOMMERZ callbacks — accept GET (redirect) AND POST (form-post)
                Route::match(['GET','POST'], 'success', [PaymentController::class, 'success'])->name('success');
                Route::match(['GET','POST'], 'fail',    [PaymentController::class, 'fail'])->name('fail');
                Route::match(['GET','POST'], 'cancel',  [PaymentController::class, 'cancel'])->name('cancel');

                Route::get('{payment}/receipt',      [PaymentController::class, 'receipt'])->name('receipt');
                Route::get('history',                [PaymentController::class, 'history'])->name('history');
                Route::post('{plan}/subscribe-free', [PaymentController::class, 'subscribeFree'])->name('subscribe-free');
            });

            // Documentation
            Route::get('docs',          [DocumentationController::class, 'index'])->name('docs');

            // Channel Auth
            Route::get('reverb/auth',   ReverbChannelAuthController::class)->name('reverb.auth');
        });
    });

// ── Admin panel ───────────────────────────────────────────────────────
Route::middleware(['auth', 'active.user', 'admin.area'])
    ->prefix('admin')->name('admin.')
    ->group(function () {
        Route::get('/',       [AdminDashboardController::class, 'index'])->name('dashboard');

        // ── User Management ──────────────────────────────────────────────────
        // NOTE: create/store MUST be registered before {user} to avoid "create" being matched as a user ID

        // Create users
        Route::middleware('permission:users.create')->group(function () {
            Route::get('users/create',  [UserController::class, 'create'])->name('users.create');
            Route::post('users',        [UserController::class, 'store'])->name('users.store');
        });

        // View users
        Route::middleware('permission:users.view')->group(function () {
            Route::get('users',         [UserController::class, 'index'])->name('users.index');
            Route::get('users/{user}',  [UserController::class, 'show'])->name('users.show');
        });

        // Edit users
        Route::middleware('permission:users.edit')->group(function () {
            Route::get('users/{user}/edit',     [UserController::class, 'edit'])->name('users.edit');
            Route::put('users/{user}',          [UserController::class, 'update'])->name('users.update');
            Route::patch('users/{user}',        [UserController::class, 'update']);
            Route::post('users/{user}/toggle-ban', [UserController::class, 'toggleBan'])->name('users.toggle-ban');
        });

        // Assign subscription plan
        Route::middleware('permission:subscriptions.assign')->group(function () {
            Route::post('users/{user}/assign-plan', [UserController::class, 'assignPlan'])->name('users.assign-plan');
        });

        // Delete users
        Route::middleware('permission:users.delete')->group(function () {
            Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        });

        // Apps
        Route::middleware('permission:apps.view')->group(function () {
            Route::resource('apps', AdminAppController::class)->except(['create', 'store']);
        });

        // Events
        Route::middleware('permission:events.view')->group(function () {
            Route::get('events',              [EventController::class, 'index'])->name('events.index');
            Route::get('events/{event}',      [EventController::class, 'show'])->name('events.show');
            Route::post('events/{event}/retry', [EventController::class, 'retry'])->name('events.retry');
        });

        // Superadmin only
        Route::middleware('superadmin')->group(function () {
            Route::resource('roles',    RoleController::class);
            Route::resource('plans',    AdminPlanController::class);
            Route::get('settings',      [AdminSettingsController::class, 'index'])->name('settings');
            Route::post('settings',     [AdminSettingsController::class, 'update'])->name('settings.update');
        });
    });


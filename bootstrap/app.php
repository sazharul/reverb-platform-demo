<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'superadmin'    => \App\Http\Middleware\SuperAdminMiddleware::class,
            'admin.area'    => \App\Http\Middleware\AdminAreaMiddleware::class,
            'active.user'   => \App\Http\Middleware\UserActiveMiddleware::class,

            'role'          => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'    => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        // SSLCOMMERZ sends POST/GET callbacks — exclude from CSRF
        $middleware->validateCsrfTokens(except: [
            'payments/ipn',
            'user/payments/success',
            'user/payments/fail',
            'user/payments/cancel',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

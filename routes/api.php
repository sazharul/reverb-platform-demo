<?php

use App\Http\Controllers\Api\V1\AuthTokenController;
use App\Http\Controllers\Api\V1\EventTriggerController;
use App\Http\Controllers\Api\V1\ReverbChannelAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/auth/token', [AuthTokenController::class, 'store']);
    Route::post('/events', [EventTriggerController::class, 'store']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/reverb/auth', ReverbChannelAuthController::class);
    });
});

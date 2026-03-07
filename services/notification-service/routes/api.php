<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes – Notification Service
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    Route::get('/health', [HealthCheckController::class, 'check']);

    Route::middleware('auth:api')->group(function () {
        Route::apiResource('webhooks', WebhookController::class);
        Route::post('webhooks/{id}/test', [WebhookController::class, 'test']);
        Route::get('webhooks/{id}/logs',  [WebhookController::class, 'logs']);

        Route::post('notify', [NotificationController::class, 'send']);
    });

    // Internal endpoint – called by other services (network-level protection only)
    Route::post('internal/events', [EventController::class, 'handle']);
});

<?php

use App\Modules\Order\Controllers\OrderController;
use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Order Module
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->middleware(['api', 'keycloak.auth'])->group(function () {

    // Order listing and retrieval
    Route::get('orders', [OrderController::class, 'index']);
    Route::get('orders/{id}', [OrderController::class, 'show']);

    // Create order (triggers Saga)
    Route::post('orders', [OrderController::class, 'store']);

    // Order status management
    Route::patch('orders/{id}/status', [OrderController::class, 'updateStatus'])
        ->middleware('keycloak.role:admin,warehouse-manager');

    // Order cancellation
    Route::post('orders/{id}/cancel', [OrderController::class, 'cancel']);
});

// Health check (no auth required)
Route::get('/health', [HealthController::class, 'check']);

<?php

use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Health check – public
    |--------------------------------------------------------------------------
    */
    Route::get('/health', HealthCheckController::class)->name('health');

    /*
    |--------------------------------------------------------------------------
    | Protected order routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['tenant', 'auth:api'])->group(function () {
        // Standard CRUD (no update – orders are immutable post-creation)
        Route::apiResource('orders', OrderController::class)
            ->only(['index', 'show', 'store']);

        // Order actions
        Route::post('orders/{id}/cancel',      [OrderController::class, 'cancel'])     ->name('orders.cancel');
        Route::get('orders/{id}/saga-status',  [OrderController::class, 'sagaStatus']) ->name('orders.saga-status');
    });
});

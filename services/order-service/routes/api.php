<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Order Service API Routes
|--------------------------------------------------------------------------
*/

// Health checks
Route::prefix('health')->group(function (): void {
    Route::get('/', [\App\Http\Controllers\Order\HealthController::class, 'check'])->name('health.check');
    Route::get('/ready', [\App\Http\Controllers\Order\HealthController::class, 'ready'])->name('health.ready');
});

Route::prefix('v1')
    ->middleware(['auth:api', \App\Http\Middleware\TenantMiddleware::class])
    ->group(function (): void {

        // Order lifecycle
        Route::prefix('orders')->name('orders.')->group(function (): void {
            Route::get('/', [\App\Http\Controllers\Order\OrderController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Order\OrderController::class, 'store'])->name('store');
            Route::get('/{id}', [\App\Http\Controllers\Order\OrderController::class, 'show'])->name('show');
            Route::post('/{id}/cancel', [\App\Http\Controllers\Order\OrderController::class, 'cancel'])->name('cancel');
        });
    });

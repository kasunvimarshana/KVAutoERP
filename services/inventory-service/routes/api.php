<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Inventory Service API Routes
|--------------------------------------------------------------------------
*/

// Health checks
Route::prefix('health')->group(function (): void {
    Route::get('/', [\App\Http\Controllers\Health\HealthController::class, 'check'])->name('health.check');
    Route::get('/ready', [\App\Http\Controllers\Health\HealthController::class, 'ready'])->name('health.ready');
});

Route::prefix('v1')
    ->middleware(['auth:api', \App\Http\Middleware\TenantMiddleware::class])
    ->group(function (): void {

        // Inventory management
        Route::prefix('inventory')->name('inventory.')->group(function (): void {
            Route::get('/', [\App\Http\Controllers\Inventory\InventoryController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Inventory\InventoryController::class, 'store'])->name('store');
            Route::get('/low-stock', [\App\Http\Controllers\Inventory\InventoryController::class, 'lowStock'])->name('low-stock');
            Route::get('/{id}', [\App\Http\Controllers\Inventory\InventoryController::class, 'show'])->name('show');
            Route::put('/{id}', [\App\Http\Controllers\Inventory\InventoryController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\Inventory\InventoryController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/reserve', [\App\Http\Controllers\Inventory\InventoryController::class, 'reserve'])->name('reserve');
            Route::post('/{id}/release', [\App\Http\Controllers\Inventory\InventoryController::class, 'release'])->name('release');
        });

        // Category management
        Route::apiResource('categories', \App\Http\Controllers\Category\CategoryController::class);

        // Warehouse management
        Route::apiResource('warehouses', \App\Http\Controllers\Warehouse\WarehouseController::class);

        // Webhook management
        Route::prefix('webhooks')->name('webhooks.')->group(function (): void {
            Route::get('/', [\App\Http\Controllers\WebhookController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\WebhookController::class, 'store'])->name('store');
            Route::put('/{id}', [\App\Http\Controllers\WebhookController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\WebhookController::class, 'destroy'])->name('destroy');
        });
    });

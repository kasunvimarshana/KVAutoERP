<?php

use App\Modules\Inventory\Controllers\InventoryController;
use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Inventory Module
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->middleware(['api', 'keycloak.auth'])->group(function () {

    // Inventory CRUD
    Route::apiResource('inventory', InventoryController::class)->parameters([
        'inventory' => 'id',
    ]);

    // Get inventory for a specific product
    Route::get('inventory/product/{productId}', [InventoryController::class, 'showByProduct']);

    // Adjust inventory quantity
    Route::post('inventory/product/{productId}/adjust', [InventoryController::class, 'adjust']);
});

// Health check (no auth required)
Route::get('/health', [HealthController::class, 'check']);

// Internal service-to-service routes
Route::prefix('internal/v1')->middleware(['api', 'service.auth'])->group(function () {
    Route::get('/inventory/product/{productId}', [InventoryController::class, 'showByProduct']);
    Route::post('/inventory/product/{productId}/reserve', [InventoryController::class, 'reserve']);
    Route::post('/inventory/product/{productId}/release', [InventoryController::class, 'release']);
});

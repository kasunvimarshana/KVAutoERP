<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\HealthController;

Route::get('/health', [HealthController::class, 'check']);

Route::prefix('inventory')->group(function () {
    // Standard CRUD
    Route::get('/',        [InventoryController::class, 'index']);
    Route::post('/',       [InventoryController::class, 'store']);
    Route::get('/{id}',    [InventoryController::class, 'show']);
    Route::put('/{id}',    [InventoryController::class, 'update']);
    Route::delete('/{id}', [InventoryController::class, 'destroy']);

    // Stock management
    Route::post('/{id}/adjust', [InventoryController::class, 'adjust']);

    // Saga endpoints (called by Order Service)
    Route::post('/reserve', [InventoryController::class, 'reserve']);
    Route::post('/release', [InventoryController::class, 'release']);
    Route::post('/confirm', [InventoryController::class, 'confirm']);
});

<?php

use App\Modules\Inventory\Controllers\InventoryController;
use App\Modules\Inventory\Webhooks\InventoryWebhookHandler;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth.keycloak', 'tenant'])->prefix('inventory')->group(function () {
    Route::get('/', [InventoryController::class, 'index']);
    Route::post('/', [InventoryController::class, 'store']);
    Route::get('/{id}', [InventoryController::class, 'show']);
    Route::put('/{id}', [InventoryController::class, 'update']);
    Route::delete('/{id}', [InventoryController::class, 'destroy']);
    Route::post('/{id}/adjust', [InventoryController::class, 'adjustStock']);
    Route::post('/{id}/reserve', [InventoryController::class, 'reserveStock']);
    Route::post('/{id}/release', [InventoryController::class, 'releaseStock']);
});

Route::middleware(['verify.service'])->prefix('webhooks/inventory')->group(function () {
    Route::post('/', [InventoryWebhookHandler::class, 'handle']);
});

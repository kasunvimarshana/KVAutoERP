<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Inventory\Infrastructure\Http\Controllers\BatchLotController;
use Modules\Inventory\Infrastructure\Http\Controllers\CycleCountController;
use Modules\Inventory\Infrastructure\Http\Controllers\InventoryAdjustmentController;
use Modules\Inventory\Infrastructure\Http\Controllers\StockController;
use Modules\Inventory\Infrastructure\Http\Controllers\StockMovementController;

Route::prefix('api')->middleware('auth:api')->group(function (): void {
    Route::apiResource('stocks', StockController::class)->only(['index', 'show']);
    Route::apiResource('stock-movements', StockMovementController::class)->only(['index', 'store', 'show']);

    Route::apiResource('batch-lots', BatchLotController::class);
    Route::post('batch-lots/{id}/quarantine', [BatchLotController::class, 'quarantine']);
    Route::post('batch-lots/{id}/consume', [BatchLotController::class, 'consume']);

    Route::apiResource('inventory-adjustments', InventoryAdjustmentController::class);
    Route::post('inventory-adjustments/{id}/approve', [InventoryAdjustmentController::class, 'approve']);
    Route::post('inventory-adjustments/{id}/apply', [InventoryAdjustmentController::class, 'apply']);

    Route::apiResource('cycle-counts', CycleCountController::class);
});

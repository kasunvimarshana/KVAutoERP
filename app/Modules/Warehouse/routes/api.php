<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Warehouse\Infrastructure\Http\Controllers\WarehouseController;
use Modules\Warehouse\Infrastructure\Http\Controllers\WarehouseLocationController;

Route::prefix('api')->middleware(['api'])->group(function () {
    Route::get('/warehouses', [WarehouseController::class, 'index']);
    Route::post('/warehouses', [WarehouseController::class, 'store']);
    Route::get('/warehouses/{id}', [WarehouseController::class, 'show']);
    Route::put('/warehouses/{id}', [WarehouseController::class, 'update']);
    Route::delete('/warehouses/{id}', [WarehouseController::class, 'destroy']);
    Route::get('/warehouses/{id}/locations/tree', [WarehouseLocationController::class, 'tree']);

    Route::get('/warehouse-locations', [WarehouseLocationController::class, 'index']);
    Route::post('/warehouse-locations', [WarehouseLocationController::class, 'store']);
    Route::get('/warehouse-locations/{id}', [WarehouseLocationController::class, 'show']);
    Route::put('/warehouse-locations/{id}', [WarehouseLocationController::class, 'update']);
    Route::delete('/warehouse-locations/{id}', [WarehouseLocationController::class, 'destroy']);
    Route::post('/warehouse-locations/{id}/move', [WarehouseLocationController::class, 'move']);
});

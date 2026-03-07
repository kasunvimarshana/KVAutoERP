<?php

use App\Modules\Inventory\Controllers\InventoryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'tenant'])->prefix('inventory')->group(function () {
    Route::get('/', [InventoryController::class, 'index']);
    Route::post('/', [InventoryController::class, 'store'])->middleware('permission:create-inventory');
    Route::get('/{inventory}', [InventoryController::class, 'show']);
    Route::put('/{inventory}', [InventoryController::class, 'update'])->middleware('permission:edit-inventory');
    Route::delete('/{inventory}', [InventoryController::class, 'destroy'])->middleware('permission:delete-inventory');
    Route::post('/{inventory}/adjust', [InventoryController::class, 'adjust'])->middleware('permission:edit-inventory');
});

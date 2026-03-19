<?php

use App\Http\Controllers\InventoryController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/inventory')->group(function () {
    Route::middleware('auth:api')->group(function () {
        Route::get('/', [InventoryController::class, 'index']);
        Route::get('/{id}', [InventoryController::class, 'show']);
        Route::post('/update', [InventoryController::class, 'update']);
        Route::post('/reserve', [InventoryController::class, 'reserve']); // For Saga
        Route::post('/release/{id}', [InventoryController::class, 'release']); // For Saga rollback
    });
});
Route::get('/health', function () { return response()->json(['status' => 'ok', 'service' => 'inventory-service', 'timestamp' => now()]); });

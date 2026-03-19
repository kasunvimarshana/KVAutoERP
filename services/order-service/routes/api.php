<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/orders')->group(function () {
    Route::middleware('auth:api')->group(function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::get('/{id}', [OrderController::class, 'show']);
        Route::post('/', [OrderController::class, 'store']);
    });
});
Route::get('/health', function () { return response()->json(['status' => 'ok', 'service' => 'order-service', 'timestamp' => now()]); });

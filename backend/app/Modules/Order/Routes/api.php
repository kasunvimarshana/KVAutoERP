<?php

use App\Modules\Order\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'tenant'])->prefix('orders')->group(function () {
    Route::get('/', [OrderController::class, 'index']);
    Route::post('/', [OrderController::class, 'store'])->middleware('permission:create-orders');
    Route::get('/{order}', [OrderController::class, 'show']);
    Route::patch('/{order}/status', [OrderController::class, 'updateStatus'])->middleware('permission:edit-orders');
    Route::delete('/{order}', [OrderController::class, 'destroy'])->middleware('permission:delete-orders');
});

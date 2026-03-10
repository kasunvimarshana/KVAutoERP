<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\HealthController;
Route::get('/health', [HealthController::class, 'check']);
Route::prefix('orders')->group(function () {
    Route::get('/',               [OrderController::class, 'index']);
    Route::post('/',              [OrderController::class, 'store']);
    Route::get('/{id}',           [OrderController::class, 'show']);
    Route::put('/{id}',           [OrderController::class, 'update']);
    Route::delete('/{id}',        [OrderController::class, 'destroy']);
    Route::post('/{id}/cancel',   [OrderController::class, 'cancel']);
    Route::post('/{id}/confirm',  [OrderController::class, 'confirm']);
});

<?php

use App\Modules\Order\Controllers\OrderController;
use App\Modules\Order\Webhooks\OrderWebhookHandler;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth.keycloak', 'tenant'])->prefix('orders')->group(function () {
    Route::get('/', [OrderController::class, 'index']);
    Route::post('/', [OrderController::class, 'store']);
    Route::get('/{id}', [OrderController::class, 'show']);
    Route::put('/{id}', [OrderController::class, 'update']);
    Route::delete('/{id}', [OrderController::class, 'destroy']);
    Route::post('/{id}/cancel', [OrderController::class, 'cancel']);
    Route::post('/{id}/complete', [OrderController::class, 'complete']);
});

Route::middleware(['verify.service'])->prefix('webhooks/orders')->group(function () {
    Route::post('/', [OrderWebhookHandler::class, 'handle']);
});

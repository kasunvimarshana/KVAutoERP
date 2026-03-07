<?php

use App\Http\Controllers\HealthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SagaController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class);

Route::middleware(['App\Middleware\TenantMiddleware', 'auth:api'])->group(function (): void {
    Route::apiResource('orders', OrderController::class);
    Route::post('orders/{id}/cancel', [OrderController::class, 'cancel']);
    Route::patch('orders/{id}/status', [OrderController::class, 'updateStatus']);

    Route::get('sagas/{sagaId}', [SagaController::class, 'getSagaStatus']);
    Route::post('sagas/{sagaId}/retry', [SagaController::class, 'retryFailedSaga']);
});

<?php

use App\Http\Controllers\HealthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class);

Route::post('/webhooks/receive', [WebhookController::class, 'receiveWebhook']);

Route::middleware(['App\Middleware\TenantMiddleware', 'auth:api'])->group(function (): void {
    Route::get('products/sku/{sku}', [ProductController::class, 'getBySku']);
    Route::apiResource('products', ProductController::class);
    Route::post('webhooks/send', [WebhookController::class, 'sendWebhook']);
});

Route::middleware(['App\Middleware\TenantMiddleware', 'App\Middleware\VerifyServiceToken'])->group(function (): void {
    Route::get('/internal/products', [ProductController::class, 'internalIndex']);
});

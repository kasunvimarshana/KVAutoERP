<?php

use App\Http\Controllers\HealthController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

// Health check
Route::get('/health', [HealthController::class, 'check']);

// Webhooks
Route::prefix('webhooks')->group(function () {
    Route::post('/users', [WebhookController::class, 'handleUserWebhook']);
    Route::post('/products', [WebhookController::class, 'handleProductWebhook']);
});

// Module routes
require __DIR__ . '/../app/Modules/User/Routes/api.php';
require __DIR__ . '/../app/Modules/Product/Routes/api.php';
require __DIR__ . '/../app/Modules/Inventory/Routes/api.php';
require __DIR__ . '/../app/Modules/Order/Routes/api.php';

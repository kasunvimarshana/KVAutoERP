<?php

use App\Modules\Product\Controllers\ProductController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Product Module
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->middleware(['api', 'keycloak.auth'])->group(function () {

    // Product CRUD
    Route::apiResource('products', ProductController::class)->parameters([
        'products' => 'id',
    ]);

    // Webhook management
    Route::prefix('webhooks')->group(function () {
        Route::post('/', [WebhookController::class, 'register']);
        Route::get('/', [WebhookController::class, 'index']);
        Route::delete('/{id}', [WebhookController::class, 'destroy']);
    });
});

// Health check (no auth required)
Route::get('/health', [HealthController::class, 'check']);

// Internal service-to-service routes (validated via service JWT)
Route::prefix('internal/v1')->middleware(['api', 'service.auth'])->group(function () {
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::get('/products', [ProductController::class, 'index']);
});

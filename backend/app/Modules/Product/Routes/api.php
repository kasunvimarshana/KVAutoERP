<?php

use App\Modules\Product\Controllers\ProductController;
use App\Modules\Product\Webhooks\ProductWebhookHandler;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth.keycloak', 'tenant'])->prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::post('/', [ProductController::class, 'store']);
    Route::get('/{id}', [ProductController::class, 'show']);
    Route::put('/{id}', [ProductController::class, 'update']);
    Route::delete('/{id}', [ProductController::class, 'destroy']);
    Route::post('/{id}/restore', [ProductController::class, 'restore']);
});

Route::middleware(['verify.service'])->prefix('webhooks/products')->group(function () {
    Route::post('/', [ProductWebhookHandler::class, 'handle']);
});

<?php

declare(strict_types=1);

use App\Presentation\Controllers\ProductController;
use App\Presentation\Controllers\CategoryController;
use App\Presentation\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

Route::get('/health', [HealthController::class, 'health']);
Route::get('/health/ready', [HealthController::class, 'ready']);

Route::middleware(['tenant', 'auth.service'])->group(function () {
    Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');
    Route::apiResource('products', ProductController::class);
    Route::get('/categories/{categoryId}/products', [ProductController::class, 'byCategory']);
    Route::apiResource('categories', CategoryController::class);
});

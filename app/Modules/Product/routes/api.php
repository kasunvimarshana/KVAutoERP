<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Product\Infrastructure\Http\Controllers\ProductAttributeController;
use Modules\Product\Infrastructure\Http\Controllers\ProductCategoryController;
use Modules\Product\Infrastructure\Http\Controllers\ProductController;
use Modules\Product\Infrastructure\Http\Controllers\ProductVariantController;

Route::prefix('api')->middleware(['api'])->group(function () {
    Route::get('/product-categories/tree', [ProductCategoryController::class, 'tree']);
    Route::get('/product-categories', [ProductCategoryController::class, 'index']);
    Route::post('/product-categories', [ProductCategoryController::class, 'store']);
    Route::get('/product-categories/{id}', [ProductCategoryController::class, 'show']);
    Route::put('/product-categories/{id}', [ProductCategoryController::class, 'update']);
    Route::delete('/product-categories/{id}', [ProductCategoryController::class, 'destroy']);

    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);

    Route::get('/product-variants', [ProductVariantController::class, 'index']);
    Route::post('/product-variants', [ProductVariantController::class, 'store']);
    Route::get('/product-variants/{id}', [ProductVariantController::class, 'show']);
    Route::put('/product-variants/{id}', [ProductVariantController::class, 'update']);
    Route::delete('/product-variants/{id}', [ProductVariantController::class, 'destroy']);

    Route::get('/product-attributes', [ProductAttributeController::class, 'index']);
    Route::post('/product-attributes', [ProductAttributeController::class, 'store']);
    Route::get('/product-attributes/{id}', [ProductAttributeController::class, 'show']);
    Route::put('/product-attributes/{id}', [ProductAttributeController::class, 'update']);
    Route::delete('/product-attributes/{id}', [ProductAttributeController::class, 'destroy']);
});

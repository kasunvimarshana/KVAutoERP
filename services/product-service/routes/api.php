<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HealthController;

Route::get('/health', [HealthController::class, 'check']);

// Categories
Route::prefix('categories')->group(function () {
    Route::get('/',        [CategoryController::class, 'index']);
    Route::post('/',       [CategoryController::class, 'store']);
    Route::get('/{id}',    [CategoryController::class, 'show']);
    Route::put('/{id}',    [CategoryController::class, 'update']);
    Route::delete('/{id}', [CategoryController::class, 'destroy']);
});

// Products
Route::prefix('products')->group(function () {
    Route::get('/',        [ProductController::class, 'index']);
    Route::post('/',       [ProductController::class, 'store']);
    // Cross-service lookup: GET /api/products/lookup?ids[]=uuid&codes[]=CODE
    Route::get('/lookup',  [ProductController::class, 'lookup']);
    Route::get('/{id}',    [ProductController::class, 'show']);
    Route::put('/{id}',    [ProductController::class, 'update']);
    Route::delete('/{id}', [ProductController::class, 'destroy']);
});

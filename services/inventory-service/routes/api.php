<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

// Health (unauthenticated)
Route::get('/health',      [HealthController::class, 'health']);
Route::get('/health/ping', [HealthController::class, 'ping']);

// Protected routes
Route::middleware(['auth.jwt', 'tenant'])->group(function () {

    // Products
    Route::get('/products',    [ProductController::class, 'index']);
    Route::post('/products',   [ProductController::class, 'store']);
    Route::get('/products/{id}',    [ProductController::class, 'show']);
    Route::put('/products/{id}',    [ProductController::class, 'update']);
    Route::patch('/products/{id}',  [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);

    // Stock operations
    Route::post('/products/{id}/stock/adjust',   [StockController::class, 'adjust']);
    Route::post('/products/{id}/stock/reserve',  [StockController::class, 'reserve']);
    Route::post('/products/{id}/stock/release',  [StockController::class, 'release']);
    Route::get('/products/{id}/stock/movements', [StockController::class, 'movements']);

    // Categories
    Route::get('/categories',         [CategoryController::class, 'index']);
    Route::post('/categories',        [CategoryController::class, 'store']);
    Route::get('/categories/{id}',    [CategoryController::class, 'show']);
    Route::put('/categories/{id}',    [CategoryController::class, 'update']);
    Route::patch('/categories/{id}',  [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

    // Warehouses
    Route::get('/warehouses',      [WarehouseController::class, 'index']);
    Route::post('/warehouses',     [WarehouseController::class, 'store']);
    Route::get('/warehouses/{id}', [WarehouseController::class, 'show']);
    Route::put('/warehouses/{id}', [WarehouseController::class, 'update']);

    // Reports
    Route::get('/reports/inventory',  [ReportController::class, 'inventoryReport']);
    Route::get('/reports/low-stock',  [ReportController::class, 'lowStockReport']);
    Route::get('/reports/movements',  [ReportController::class, 'movementReport']);
    Route::get('/reports/valuation',  [ReportController::class, 'valuationReport']);
});

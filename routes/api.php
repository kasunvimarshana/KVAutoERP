<?php

use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Laravel Microservices CRUD
|--------------------------------------------------------------------------
|
| Service A (Product Service):
|   Products support full CRUD. Each mutating operation dispatches a
|   domain event consumed by Service B for inventory management.
|
| Service B (Inventory Service):
|   Inventory records are created/updated/deleted via Service A events.
|   Additional endpoints allow direct inventory management.
|
*/

/*
|--------------------------------------------------------------------------
| Service A: Product Service Endpoints
|--------------------------------------------------------------------------
*/
Route::apiResource('products', ProductController::class);

/*
|--------------------------------------------------------------------------
| Service B: Inventory Service Endpoints
|--------------------------------------------------------------------------
*/

// List all inventories (supports ?product_name= filtering)
Route::get('inventories', [InventoryController::class, 'index']);

// Update inventory record(s) by product name
Route::patch('inventories/by-product-name', [InventoryController::class, 'updateByProductName']);

// Show, update, delete a specific inventory record
Route::get('inventories/{inventory}', [InventoryController::class, 'show']);
Route::put('inventories/{inventory}', [InventoryController::class, 'update']);
Route::delete('inventories/{inventory}', [InventoryController::class, 'destroy']);

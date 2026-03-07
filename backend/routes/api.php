<?php

use App\Modules\Auth\Controllers\AuthController;
use App\Modules\Inventory\Controllers\InventoryController;
use App\Modules\Order\Controllers\OrderController;
use App\Modules\Product\Controllers\ProductController;
use App\Modules\Tenant\Controllers\TenantConfigController;
use App\Modules\User\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Auth Routes
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('login',    [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:api')->group(function () {

    // Auth management
    Route::prefix('auth')->group(function () {
        Route::post('logout',    [AuthController::class, 'logout']);
        Route::post('refresh',   [AuthController::class, 'refresh']);
        Route::get('me',         [AuthController::class, 'me']);
        Route::post('sso-token', [AuthController::class, 'ssoToken']);
    });

    // Tenant-scoped routes (all require a resolved tenant)
    Route::middleware('tenant')->group(function () {

        // Tenant runtime configuration
        Route::prefix('tenant/config')->group(function () {
            Route::get('/',          [TenantConfigController::class, 'index']);
            Route::post('/',         [TenantConfigController::class, 'upsert']);
            Route::delete('/{key}',  [TenantConfigController::class, 'destroy']);
        });

        // User management (CRUD + role assignment)
        Route::prefix('users')->group(function () {
            Route::get('/',                     [UserController::class, 'index']);
            Route::post('/',                    [UserController::class, 'store']);
            Route::get('/{id}',                 [UserController::class, 'show']);
            Route::put('/{id}',                 [UserController::class, 'update']);
            Route::delete('/{id}',              [UserController::class, 'destroy']);
            Route::post('/{id}/roles/assign',   [UserController::class, 'assignRole']);
            Route::post('/{id}/roles/revoke',   [UserController::class, 'revokeRole']);
        });

        // Products
        Route::prefix('products')->group(function () {
            Route::get('/',       [ProductController::class, 'index']);
            Route::post('/',      [ProductController::class, 'store']);
            Route::get('/{id}',   [ProductController::class, 'show']);
            Route::put('/{id}',   [ProductController::class, 'update']);
            Route::delete('/{id}',[ProductController::class, 'destroy']);
        });

        // Inventory (includes cross-service product-name filter)
        Route::prefix('inventory')->group(function () {
            Route::get('/',                    [InventoryController::class, 'index']);
            Route::post('/',                   [InventoryController::class, 'store']);
            Route::get('/{id}',                [InventoryController::class, 'show']);
            Route::put('/{id}',                [InventoryController::class, 'update']);
            Route::delete('/{id}',             [InventoryController::class, 'destroy']);
            Route::patch('/{id}/adjust',       [InventoryController::class, 'adjustQuantity']);
            Route::patch('/{id}/reserve',      [InventoryController::class, 'reserve']);
            Route::patch('/{id}/release',      [InventoryController::class, 'release']);
        });

        // Orders (Saga-based placement + cancellation)
        Route::prefix('orders')->group(function () {
            Route::get('/',              [OrderController::class, 'index']);
            Route::post('/',             [OrderController::class, 'store']);
            Route::get('/{id}',          [OrderController::class, 'show']);
            Route::patch('/{id}/cancel', [OrderController::class, 'cancel']);
        });
    });
});

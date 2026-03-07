<?php

use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\InventoryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes – Inventory Service
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // Public health-check – no auth required (used by Docker / k8s probes)
    Route::get('/health', [HealthCheckController::class, 'check']);

    // Protected routes – require a valid tenant and a verified JWT
    Route::middleware(['tenant', 'auth:api'])->group(function () {

        // Low-stock report must be declared BEFORE the resource routes
        // to prevent Laravel from treating 'reports' as an {inventory} ID.
        Route::get('inventories/reports/low-stock', [InventoryController::class, 'lowStock']);

        // Standard CRUD
        Route::apiResource('inventories', InventoryController::class);

        // Stock adjustment endpoint
        Route::post('inventories/{id}/adjust-stock', [InventoryController::class, 'adjustStock']);
    });
});

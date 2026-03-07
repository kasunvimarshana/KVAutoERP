<?php

use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\TenantController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes – Tenant Service
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // Public health check (no auth required)
    Route::get('/health', [HealthCheckController::class, 'check']);

    // Authenticated routes
    Route::middleware('auth:api')->group(function () {
        Route::apiResource('tenants', TenantController::class);

        // Status transitions
        Route::post('tenants/{id}/activate', [TenantController::class, 'activate']);
        Route::post('tenants/{id}/suspend',  [TenantController::class, 'suspend']);

        // Config management
        Route::get('tenants/{id}/config',          [TenantController::class, 'getConfig']);
        Route::put('tenants/{id}/config',          [TenantController::class, 'updateConfig']);
        Route::post('tenants/{id}/config/refresh', [TenantController::class, 'refreshConfig']);
    });

    // Internal endpoints – called by sibling services (no auth, network-level protection only)
    Route::prefix('internal')->group(function () {
        Route::get('tenants/{id}',              [TenantController::class, 'show']);
        Route::get('tenants/domain/{domain}',   [TenantController::class, 'findByDomain']);
    });
});

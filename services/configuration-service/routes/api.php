<?php

declare(strict_types=1);

use App\Http\Controllers\FeatureFlagController;
use App\Http\Controllers\FormDefinitionController;
use App\Http\Controllers\ModuleRegistryController;
use App\Http\Controllers\TenantConfigurationController;
use App\Http\Controllers\WorkflowDefinitionController;
use App\Http\Middleware\VerifyServiceToken;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Configuration Service API Routes  –  /api/v1/...
|--------------------------------------------------------------------------
*/

Route::prefix('api/v1')->group(function () {

    // ----------------------------------------------------------------
    // Health Check (unauthenticated)
    // ----------------------------------------------------------------
    Route::get('/health', fn () => response()->json([
        'status'  => 'ok',
        'service' => 'configuration-service',
        'version' => '1.0.0',
        'time'    => now()->toIso8601String(),
    ]));

    // ----------------------------------------------------------------
    // All configuration endpoints require a valid service JWT
    // ----------------------------------------------------------------
    Route::middleware(VerifyServiceToken::class)->group(function () {

        // Tenant Configurations
        // GET  /api/v1/config/{tenantId}/{service}  — service config map
        Route::get('/config/{tenantId}/{service}', [TenantConfigurationController::class, 'getServiceConfig']);

        Route::prefix('config')->group(function () {
            Route::get('/',          [TenantConfigurationController::class, 'index']);
            Route::post('/',         [TenantConfigurationController::class, 'store']);
            Route::get('/{id}',      [TenantConfigurationController::class, 'show']);
            Route::put('/{id}',      [TenantConfigurationController::class, 'update']);
            Route::delete('/{id}',   [TenantConfigurationController::class, 'destroy']);
        });

        // Feature Flags
        // GET /api/v1/features/check/{flagKey} must be registered BEFORE /{id}
        Route::prefix('features')->group(function () {
            Route::get('/check/{flagKey}', [FeatureFlagController::class, 'check']);
            Route::get('/',               [FeatureFlagController::class, 'index']);
            Route::post('/',              [FeatureFlagController::class, 'store']);
            Route::get('/{id}',           [FeatureFlagController::class, 'show']);
            Route::put('/{id}',           [FeatureFlagController::class, 'update']);
            Route::delete('/{id}',        [FeatureFlagController::class, 'destroy']);
            Route::post('/{id}/toggle',   [FeatureFlagController::class, 'toggle']);
        });

        // Form Definitions
        Route::prefix('forms')->group(function () {
            Route::get('/',        [FormDefinitionController::class, 'index']);
            Route::post('/',       [FormDefinitionController::class, 'store']);
            Route::get('/{id}',    [FormDefinitionController::class, 'show']);
            Route::put('/{id}',    [FormDefinitionController::class, 'update']);
            Route::delete('/{id}', [FormDefinitionController::class, 'destroy']);
        });

        // Workflow Definitions
        Route::prefix('workflows')->group(function () {
            Route::get('/',        [WorkflowDefinitionController::class, 'index']);
            Route::post('/',       [WorkflowDefinitionController::class, 'store']);
            Route::get('/{id}',    [WorkflowDefinitionController::class, 'show']);
            Route::put('/{id}',    [WorkflowDefinitionController::class, 'update']);
            Route::delete('/{id}', [WorkflowDefinitionController::class, 'destroy']);
        });

        // Module Registry
        Route::prefix('modules')->group(function () {
            Route::get('/',              [ModuleRegistryController::class, 'index']);
            Route::post('/',             [ModuleRegistryController::class, 'store']);
            Route::get('/{id}',          [ModuleRegistryController::class, 'show']);
            Route::put('/{id}',          [ModuleRegistryController::class, 'update']);
            Route::delete('/{id}',       [ModuleRegistryController::class, 'destroy']);
            Route::post('/{id}/toggle',  [ModuleRegistryController::class, 'toggle']);
        });
    });
});

<?php

declare(strict_types=1);

use App\Http\Controllers\HealthController;
use App\Http\Controllers\TenantConfigController;
use App\Http\Controllers\TenantController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tenant Service API Routes
|--------------------------------------------------------------------------
|
| All routes are prefixed with /api (set in bootstrap/app.php).
| Super-admin routes for tenant CRUD are protected by the 'super-admin'
| middleware group. Health endpoints are public.
|
*/

// ─────────────────────────────────────────────────────────────────────────────
// Health endpoints — no authentication required.
// ─────────────────────────────────────────────────────────────────────────────

Route::get('/health', [HealthController::class, 'index']);
Route::get('/health/ping', [HealthController::class, 'ping']);

// ─────────────────────────────────────────────────────────────────────────────
// Tenant CRUD — super-admin only.
// ─────────────────────────────────────────────────────────────────────────────

Route::middleware(['auth', 'rbac:super-admin'])->group(function (): void {
    // List & create tenants.
    Route::get('/tenants', [TenantController::class, 'index']);
    Route::post('/tenants', [TenantController::class, 'store']);

    // Get, update, and delete individual tenants.
    Route::get('/tenants/{id}', [TenantController::class, 'show']);
    Route::put('/tenants/{id}', [TenantController::class, 'update']);
    Route::delete('/tenants/{id}', [TenantController::class, 'destroy']);

    // Tenant configuration management.
    Route::get('/tenants/{tenantId}/config', [TenantConfigController::class, 'index']);
    Route::post('/tenants/{tenantId}/config', [TenantConfigController::class, 'upsert']);
    Route::get('/tenants/{tenantId}/config/{key}', [TenantConfigController::class, 'show']);
});

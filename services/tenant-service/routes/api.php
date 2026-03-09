<?php

declare(strict_types=1);

use App\Http\Controllers\Health\HealthController;
use App\Http\Controllers\Organization\OrganizationController;
use App\Http\Controllers\Tenant\TenantController;
use App\Http\Controllers\Webhook\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tenant Service API Routes
|--------------------------------------------------------------------------
*/

// ---- Health checks (no auth required) ------------------------------------
Route::get('/health', [HealthController::class, 'index'])->name('health.index');
Route::get('/health/detailed', [HealthController::class, 'detailed'])->name('health.detailed');

// ---- Tenant management ---------------------------------------------------
Route::prefix('api/tenants')->group(function (): void {
    Route::get('/',                       [TenantController::class, 'index'])->name('tenants.index');
    Route::post('/',                      [TenantController::class, 'store'])->name('tenants.store');
    Route::get('/{id}',                   [TenantController::class, 'show'])->name('tenants.show');
    Route::put('/{id}',                   [TenantController::class, 'update'])->name('tenants.update');
    Route::patch('/{id}',                 [TenantController::class, 'update'])->name('tenants.patch');
    Route::delete('/{id}',                [TenantController::class, 'destroy'])->name('tenants.destroy');
    Route::patch('/{id}/config',          [TenantController::class, 'updateConfig'])->name('tenants.config.update');
    Route::get('/{id}/health',            [TenantController::class, 'getHealth'])->name('tenants.health');
});

// ---- Organization management ---------------------------------------------
Route::prefix('api/organizations')->group(function (): void {
    Route::get('/',                       [OrganizationController::class, 'index'])->name('organizations.index');
    Route::post('/',                      [OrganizationController::class, 'store'])->name('organizations.store');
    Route::get('/hierarchy',              [OrganizationController::class, 'hierarchy'])->name('organizations.hierarchy');
    Route::get('/{id}',                   [OrganizationController::class, 'show'])->name('organizations.show');
    Route::put('/{id}',                   [OrganizationController::class, 'update'])->name('organizations.update');
    Route::patch('/{id}',                 [OrganizationController::class, 'update'])->name('organizations.patch');
    Route::delete('/{id}',                [OrganizationController::class, 'destroy'])->name('organizations.destroy');
    Route::get('/{id}/hierarchy',         [OrganizationController::class, 'hierarchy'])->name('organizations.hierarchy.id');
});

// ---- Webhook management --------------------------------------------------
Route::prefix('api/webhooks')->group(function (): void {
    Route::get('/',                       [WebhookController::class, 'index'])->name('webhooks.index');
    Route::post('/',                      [WebhookController::class, 'store'])->name('webhooks.store');
    Route::get('/{id}',                   [WebhookController::class, 'show'])->name('webhooks.show');
    Route::put('/{id}',                   [WebhookController::class, 'update'])->name('webhooks.update');
    Route::patch('/{id}',                 [WebhookController::class, 'update'])->name('webhooks.patch');
    Route::delete('/{id}',                [WebhookController::class, 'destroy'])->name('webhooks.destroy');
    Route::post('/{id}/test',             [WebhookController::class, 'test'])->name('webhooks.test');
});

<?php

declare(strict_types=1);

use App\Presentation\Controllers\AuthController;
use App\Presentation\Controllers\HealthController;
use App\Presentation\Controllers\TenantController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth Service API Routes
|--------------------------------------------------------------------------
|
| All routes follow strict REST standards.
| Authentication: Laravel Passport (OAuth2 Bearer tokens)
| Multi-tenant: X-Tenant-ID header required for most routes
|
*/

// Health check endpoints (no auth required)
Route::get('/health', [HealthController::class, 'health'])->name('health.check');
Route::get('/health/ready', [HealthController::class, 'ready'])->name('health.ready');

// Public auth endpoints (tenant required, no user auth required)
Route::middleware(['tenant'])->prefix('auth')->name('auth.')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/register', [AuthController::class, 'register'])->name('register');
});

// Protected auth endpoints (tenant + user auth required)
Route::middleware(['tenant', 'auth:api'])->prefix('auth')->name('auth.')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/me', [AuthController::class, 'me'])->name('me');
    Route::post('/introspect', [AuthController::class, 'introspect'])->name('introspect');
});

// Admin-only tenant configuration management
Route::middleware(['tenant', 'auth:api', 'ability:write'])->prefix('tenants')->name('tenants.')->group(function () {
    Route::get('/{tenantId}/config', [TenantController::class, 'getConfig'])->name('config.get');
    Route::patch('/{tenantId}/config', [TenantController::class, 'updateConfig'])->name('config.update');
    Route::patch('/{tenantId}/features', [TenantController::class, 'toggleFeature'])->name('features.toggle');
});

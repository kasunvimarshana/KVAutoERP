<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\TokenController;
use App\Http\Controllers\Tenant\TenantController;
use App\Http\Controllers\HealthController;

/*
|--------------------------------------------------------------------------
| Auth Service API Routes
|--------------------------------------------------------------------------
*/

// Health check (public)
Route::get('/health', [HealthController::class, 'check']);

// Authentication (public)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
});

// Token validation (used by other services - accepts service token)
Route::prefix('tokens')->group(function () {
    Route::post('/validate',   [TokenController::class, 'validate']);
    Route::get('/introspect',  [TokenController::class, 'introspect'])->middleware('auth:api');
});

// Protected auth routes
Route::prefix('auth')->middleware('auth:api')->group(function () {
    Route::post('/logout',          [AuthController::class, 'logout']);
    Route::get('/me',               [AuthController::class, 'me']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
});

// Tenant management
Route::prefix('tenants')->middleware(['auth:api', 'rbac:super-admin'])->group(function () {
    Route::get('/',         [TenantController::class, 'index']);
    Route::post('/',        [TenantController::class, 'store']);
    Route::get('/{id}',     [TenantController::class, 'show']);
    Route::put('/{id}',     [TenantController::class, 'update']);
    Route::delete('/{id}',  [TenantController::class, 'destroy']);
});

<?php

declare(strict_types=1);

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\SessionController;
use App\Http\Middleware\TenantAwareRateLimit;
use App\Http\Middleware\VerifyJwtToken;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth Service API Routes  –  /api/v1/auth/...
|--------------------------------------------------------------------------
*/

Route::prefix('api/v1')->group(function () {

    // ----------------------------------------------------------------
    // Health Check (unauthenticated)
    // ----------------------------------------------------------------
    Route::get('/health', fn () => response()->json([
        'status'  => 'ok',
        'service' => 'auth-service',
        'version' => '1.0.0',
        'time'    => now()->toIso8601String(),
    ]));

    // ----------------------------------------------------------------
    // Authentication – public endpoints with rate limiting
    // ----------------------------------------------------------------
    Route::prefix('auth')->group(function () {

        Route::post('/login', [AuthController::class, 'login'])
            ->middleware(TenantAwareRateLimit::class . ':login');

        Route::post('/register', [AuthController::class, 'register'])
            ->middleware(TenantAwareRateLimit::class . ':register');

        Route::post('/refresh', [AuthController::class, 'refresh'])
            ->middleware(TenantAwareRateLimit::class . ':refresh');

        Route::post('/password/forgot', [AuthController::class, 'forgotPassword']);

        Route::post('/password/reset', [AuthController::class, 'resetPassword']);

        // ----------------------------------------------------------------
        // Authenticated endpoints
        // ----------------------------------------------------------------
        Route::middleware(VerifyJwtToken::class)->group(function () {

            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/logout/all', [AuthController::class, 'logoutAll']);
            Route::get('/me', [AuthController::class, 'me']);

            // Sessions
            Route::prefix('sessions')->group(function () {
                Route::get('/', [SessionController::class, 'index']);
                Route::delete('/{sessionId}', [SessionController::class, 'destroy']);
                Route::delete('/device/{deviceId}', [SessionController::class, 'destroyDevice']);
            });

            // Roles & Permissions (admin-only in production)
            Route::prefix('roles')->group(function () {
                Route::post('/', [RolePermissionController::class, 'createRole']);
                Route::post('/{roleId}/permissions', [RolePermissionController::class, 'assignPermissionToRole']);
            });

            Route::prefix('permissions')->group(function () {
                Route::post('/', [RolePermissionController::class, 'createPermission']);
            });

            Route::prefix('users/{userId}')->group(function () {
                Route::post('/roles', [RolePermissionController::class, 'assignRole']);
                Route::delete('/roles/{roleId}', [RolePermissionController::class, 'revokeRole']);
            });

        });
    });
});

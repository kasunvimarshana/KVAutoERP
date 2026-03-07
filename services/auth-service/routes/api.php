<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HealthCheckController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Health – public
    |--------------------------------------------------------------------------
    */
    Route::get('/health', HealthCheckController::class)->name('health');

    /*
    |--------------------------------------------------------------------------
    | Public auth endpoints
    |--------------------------------------------------------------------------
    */
    Route::post('/auth/login',    [AuthController::class, 'login'])   ->name('auth.login');
    Route::post('/auth/register', [AuthController::class, 'register'])->name('auth.register');

    /*
    |--------------------------------------------------------------------------
    | Token-only (no tenant header required for refresh / validate)
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:api')->group(function () {
        Route::post('/auth/refresh',        [AuthController::class, 'refresh'])        ->name('auth.refresh');
        Route::post('/auth/logout',         [AuthController::class, 'logout'])         ->name('auth.logout');
        Route::get('/auth/me',              [AuthController::class, 'me'])             ->name('auth.me');
        Route::post('/auth/sso-token',      [AuthController::class, 'ssoToken'])       ->name('auth.sso-token');
        Route::post('/auth/validate-token', [AuthController::class, 'validateToken'])  ->name('auth.validate-token');
    });

    /*
    |--------------------------------------------------------------------------
    | Tenant-scoped protected routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:api', 'tenant.aware'])->group(function () {
        // RBAC management
        Route::post(  '/users/{id}/roles',               [AuthController::class, 'assignRole'])       ->name('users.roles.assign');
        Route::delete('/users/{id}/roles/{role}',        [AuthController::class, 'revokeRole'])        ->name('users.roles.revoke');
        Route::get(   '/users/{id}/permissions',         [AuthController::class, 'getUserPermissions'])->name('users.permissions');
    });
});

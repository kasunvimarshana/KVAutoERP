<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth Service API Routes
|--------------------------------------------------------------------------
|
| All routes are versioned under /api/v1 and grouped by tenant context.
|
*/

// Health check endpoints (no auth required)
Route::prefix('health')->group(function (): void {
    Route::get('/', [\App\Http\Controllers\HealthController::class, 'check'])->name('health.check');
    Route::get('/ready', [\App\Http\Controllers\HealthController::class, 'ready'])->name('health.ready');
});

// Public endpoints (no tenant context required)
Route::prefix('v1')->group(function (): void {

    // Authentication (tenant-resolved via middleware)
    Route::middleware([\App\Http\Middleware\TenantMiddleware::class])
        ->prefix('auth')
        ->name('auth.')
        ->group(function (): void {
            Route::post('/login', [\App\Http\Controllers\Auth\AuthController::class, 'login'])->name('login');
        });

    // Tenant management (super-admin only, no tenant middleware)
    Route::middleware(['auth:api'])
        ->prefix('tenants')
        ->name('tenants.')
        ->group(function (): void {
            Route::get('/', [\App\Http\Controllers\Tenant\TenantController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Tenant\TenantController::class, 'store'])->name('store');
            Route::get('/{id}', [\App\Http\Controllers\Tenant\TenantController::class, 'show'])->name('show');
            Route::put('/{id}', [\App\Http\Controllers\Tenant\TenantController::class, 'update'])->name('update');
            Route::patch('/{id}/configuration', [\App\Http\Controllers\Tenant\TenantController::class, 'updateConfiguration'])->name('update-config');
            Route::delete('/{id}', [\App\Http\Controllers\Tenant\TenantController::class, 'destroy'])->name('destroy');
        });

    // Protected routes (require auth + tenant context)
    Route::middleware(['auth:api', \App\Http\Middleware\TenantMiddleware::class])
        ->group(function (): void {

            // Auth profile management
            Route::prefix('auth')->name('auth.')->group(function (): void {
                Route::get('/me', [\App\Http\Controllers\Auth\AuthController::class, 'me'])->name('me');
                Route::post('/logout', [\App\Http\Controllers\Auth\AuthController::class, 'logout'])->name('logout');
                Route::post('/logout-all', [\App\Http\Controllers\Auth\AuthController::class, 'logoutAll'])->name('logout-all');
                Route::post('/refresh', [\App\Http\Controllers\Auth\AuthController::class, 'refresh'])->name('refresh');
            });

            // User management
            Route::prefix('users')->name('users.')->group(function (): void {
                Route::get('/', [\App\Http\Controllers\User\UserController::class, 'index'])->name('index');
                Route::post('/', [\App\Http\Controllers\User\UserController::class, 'store'])->name('store');
                Route::get('/{id}', [\App\Http\Controllers\User\UserController::class, 'show'])->name('show');
                Route::put('/{id}', [\App\Http\Controllers\User\UserController::class, 'update'])->name('update');
                Route::delete('/{id}', [\App\Http\Controllers\User\UserController::class, 'destroy'])->name('destroy');
                Route::post('/{id}/roles', [\App\Http\Controllers\User\UserController::class, 'assignRoles'])->name('assign-roles');
            });
        });
});

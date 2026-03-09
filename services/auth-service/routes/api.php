<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\SSOController;
use App\Http\Controllers\Health\HealthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth Service API Routes
|--------------------------------------------------------------------------
*/

// ---- Health checks (no auth or tenant required) --------------------------
Route::get('/health', [HealthController::class, 'index'])->name('health.index');
Route::get('/health/detailed', [HealthController::class, 'detailed'])->name('health.detailed');

// ---- Auth routes (tenant required) ---------------------------------------
Route::prefix('api/auth')
    ->middleware(['tenant'])
    ->group(function (): void {

        // Public endpoints
        Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
        Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
        Route::get('/verify-email/{token}', [AuthController::class, 'verifyEmail'])->name('auth.verify-email');

        // Token refresh (accepts expired token via header – no auth guard required)
        Route::post('/refresh', [AuthController::class, 'refresh'])
            ->middleware('auth:api')
            ->name('auth.refresh');

        // Protected endpoints (require valid Passport token)
        Route::middleware('auth:api')->group(function (): void {
            Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
            Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
        });

        // SSO endpoints
        Route::prefix('sso')->group(function (): void {
            Route::get('/{provider}/redirect', [SSOController::class, 'redirect'])->name('auth.sso.redirect');
            Route::get('/{provider}/callback', [SSOController::class, 'callback'])->name('auth.sso.callback');
        });

        // Internal service-to-service token validation
        // Protected by a shared service secret header (not a Passport token)
        Route::post('/validate-token', [SSOController::class, 'validateToken'])
            ->middleware('auth:api')
            ->name('auth.validate-token');
    });

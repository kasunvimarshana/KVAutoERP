<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth Service API Routes — /api/v1/auth/*
|--------------------------------------------------------------------------
*/

Route::prefix('v1/auth')->group(function (): void {

    // Public endpoints (rate-limited)
    Route::middleware(['auth.ratelimit:login'])->group(function (): void {
        Route::post('login', [AuthController::class, 'login'])
            ->name('auth.login');
    });

    Route::middleware(['auth.ratelimit:refresh'])->group(function (): void {
        Route::post('refresh', [AuthController::class, 'refresh'])
            ->name('auth.refresh');
    });

    // Protected endpoints (require valid JWT)
    Route::middleware(['jwt.verify'])->group(function (): void {
        Route::get('me', [AuthController::class, 'me'])
            ->name('auth.me');

        Route::post('logout', [AuthController::class, 'logout'])
            ->name('auth.logout');

        Route::post('revoke-device', [AuthController::class, 'revokeDevice'])
            ->name('auth.revoke-device');

        Route::post('revoke-all', [AuthController::class, 'revokeAll'])
            ->name('auth.revoke-all');
    });
});

// User management — admin-only, requires JWT
Route::prefix('v1/users')->middleware(['jwt.verify'])->group(function (): void {
    Route::post('/', [UserController::class, 'register'])
        ->name('users.register');
});

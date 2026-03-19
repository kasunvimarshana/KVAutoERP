<?php

declare(strict_types=1);

use App\Http\Controllers\UserController;
use App\Http\Controllers\UserStatusController;
use App\Http\Middleware\VerifyServiceToken;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| User Service API Routes  –  /api/v1/users/...
|--------------------------------------------------------------------------
*/

Route::prefix('api/v1')->group(function (): void {

    // ----------------------------------------------------------------
    // Health Check (unauthenticated)
    // ----------------------------------------------------------------
    Route::get('/health', fn () => response()->json([
        'status'  => 'ok',
        'service' => 'user-service',
        'version' => '1.0.0',
        'time'    => now()->toIso8601String(),
    ]));

    // ----------------------------------------------------------------
    // Authenticated endpoints – require a valid service JWT
    // ----------------------------------------------------------------
    Route::middleware(VerifyServiceToken::class)->prefix('users')->group(function (): void {

        // Collection
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);

        // Single resource
        Route::get('/{id}', [UserController::class, 'show']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class, 'destroy']);

        // Profile
        Route::get('/{id}/profile', [UserController::class, 'profile']);
        Route::put('/{id}/profile', [UserController::class, 'updateProfile']);

        // Password
        Route::post('/{id}/password', [UserController::class, 'changePassword']);

        // Status transitions
        Route::post('/{id}/activate', [UserStatusController::class, 'activate']);
        Route::post('/{id}/deactivate', [UserStatusController::class, 'deactivate']);
    });
});

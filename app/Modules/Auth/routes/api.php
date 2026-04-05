<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Auth\Infrastructure\Http\Controllers\AuthController;
use Modules\Auth\Infrastructure\Http\Controllers\UserController;

Route::prefix('api')->group(function () {
    // Auth routes (public)
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);

        Route::middleware('auth:api')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('me', [AuthController::class, 'me']);
        });
    });

    // User management routes (protected)
    Route::middleware('auth:api')->group(function () {
        Route::apiResource('users', UserController::class);
    });
});

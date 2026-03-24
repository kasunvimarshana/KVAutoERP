<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Infrastructure\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Auth Module API Routes
|--------------------------------------------------------------------------
|
| Public routes (no auth required)
| Protected routes use 'auth:api' guard (Passport)
|
*/

// Public auth endpoints
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // SSO token exchange (accepts token in request body)
    Route::post('/sso/{provider}', [AuthController::class, 'ssoExchange']);
});

// Protected auth endpoints
Route::prefix('auth')->middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});

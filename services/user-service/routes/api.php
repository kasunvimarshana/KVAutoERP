<?php

use App\Http\Controllers\HealthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class);

Route::middleware(['App\Middleware\TenantMiddleware', 'auth:api'])->group(function (): void {
    Route::apiResource('users', UserController::class);
    Route::put('users/{id}/role', [UserController::class, 'assignRole']);
    Route::put('users/{id}/permissions', [UserController::class, 'updatePermissions']);
});

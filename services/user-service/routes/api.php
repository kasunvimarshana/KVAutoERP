<?php

declare(strict_types=1);

use App\Presentation\Controllers\HealthController;
use App\Presentation\Controllers\RoleController;
use App\Presentation\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/health', [HealthController::class, 'health']);
Route::get('/health/ready', [HealthController::class, 'ready']);

Route::middleware(['tenant', 'auth.service'])->group(function () {
    Route::apiResource('users', UserController::class);
    Route::post('/users/{userId}/roles', [UserController::class, 'assignRole']);
    Route::get('/users/{userId}/permissions', [UserController::class, 'permissions']);
    Route::post('/users/{userId}/activate', [UserController::class, 'activate']);
    Route::post('/users/{userId}/deactivate', [UserController::class, 'deactivate']);

    Route::apiResource('roles', RoleController::class);
});

<?php

use App\Modules\User\Controllers\UserController;
use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - User Module
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->middleware(['api', 'keycloak.auth'])->group(function () {

    // User CRUD (admin only)
    Route::middleware('keycloak.role:admin')->group(function () {
        Route::apiResource('users', UserController::class)->parameters(['users' => 'id']);
    });

    // Profile (any authenticated user)
    Route::get('users/me', [UserController::class, 'show']);

    // RBAC/ABAC checks (for internal use)
    Route::post('users/{id}/check-role', [UserController::class, 'checkRole']);
    Route::post('users/{id}/check-attribute', [UserController::class, 'checkAttribute']);
});

// Health check (no auth required)
Route::get('/health', [HealthController::class, 'check']);

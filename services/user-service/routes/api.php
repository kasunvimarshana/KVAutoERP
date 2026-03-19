<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\PermissionController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\UserProfileController;
use App\Http\Controllers\Internal\V1\InternalUserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| User Service API Routes
|--------------------------------------------------------------------------
|
| Public health-check, internal service-to-service routes (API key auth),
| and JWT-protected user-facing API routes.
|
*/

// Public health-check — no authentication required.
Route::get('/health', static fn () => response()->json(['status' => 'ok', 'service' => 'user-service']));

// -------------------------------------------------------------------------
// Internal service-to-service (API key authentication)
// -------------------------------------------------------------------------
Route::prefix('internal/v1')
    ->middleware(['service.auth'])
    ->group(static function (): void {
        Route::get('users/{authUserId}/claims', [InternalUserController::class, 'claims'])
            ->name('internal.users.claims');
    });

// -------------------------------------------------------------------------
// JWT-protected user-facing API
// -------------------------------------------------------------------------
Route::prefix('v1')
    ->middleware(['jwt.verify'])
    ->group(static function (): void {

        // User profiles.
        Route::apiResource('users', UserProfileController::class);
        Route::post('users/{id}/roles', [UserProfileController::class, 'assignRole'])
            ->name('users.roles.assign');
        Route::delete('users/{id}/roles/{roleId}', [UserProfileController::class, 'revokeRole'])
            ->name('users.roles.revoke');

        // Roles.
        Route::apiResource('roles', RoleController::class);
        Route::post('roles/{id}/permissions', [RoleController::class, 'assignPermission'])
            ->name('roles.permissions.assign');
        Route::delete('roles/{id}/permissions/{permissionId}', [RoleController::class, 'revokePermission'])
            ->name('roles.permissions.revoke');

        // Permissions.
        Route::apiResource('permissions', PermissionController::class);
    });

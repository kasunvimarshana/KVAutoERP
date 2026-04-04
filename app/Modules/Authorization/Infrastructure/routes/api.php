<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Authorization\Infrastructure\Http\Controllers\PermissionController;
use Modules\Authorization\Infrastructure\Http\Controllers\RoleController;
use Modules\Authorization\Infrastructure\Http\Controllers\UserRoleController;

Route::prefix('roles')->group(function () {
    Route::get('/', [RoleController::class, 'index']);
    Route::post('/', [RoleController::class, 'store']);
    Route::get('/{id}', [RoleController::class, 'show']);
    Route::patch('/{id}', [RoleController::class, 'update']);
    Route::delete('/{id}', [RoleController::class, 'destroy']);
    Route::post('/{id}/assign-permission', [RoleController::class, 'assignPermission']);
    Route::post('/{id}/revoke-permission', [RoleController::class, 'revokePermission']);
});

Route::prefix('permissions')->group(function () {
    Route::get('/', [PermissionController::class, 'index']);
    Route::post('/', [PermissionController::class, 'store']);
    Route::get('/{id}', [PermissionController::class, 'show']);
});

Route::prefix('users/{userId}')->group(function () {
    Route::post('/assign-role', [UserRoleController::class, 'assignRole']);
    Route::post('/revoke-role', [UserRoleController::class, 'revokeRole']);
    Route::get('/roles', [UserRoleController::class, 'getUserRoles']);
    Route::get('/permissions', [UserRoleController::class, 'getUserPermissions']);
    Route::post('/check-permission', [UserRoleController::class, 'checkPermission']);
});

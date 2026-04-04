<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Auth\Infrastructure\Http\Controllers\PermissionController;
use Modules\Auth\Infrastructure\Http\Controllers\RoleController;
use Modules\Auth\Infrastructure\Http\Controllers\UserRoleController;

Route::prefix('api')->middleware(['api'])->group(function () {
    Route::get('/roles', [RoleController::class, 'index']);
    Route::post('/roles', [RoleController::class, 'store']);
    Route::get('/roles/{id}', [RoleController::class, 'show']);
    Route::put('/roles/{id}', [RoleController::class, 'update']);
    Route::delete('/roles/{id}', [RoleController::class, 'destroy']);
    Route::post('/roles/{id}/permissions', [RoleController::class, 'syncPermissions']);

    Route::get('/permissions', [PermissionController::class, 'index']);
    Route::post('/permissions', [PermissionController::class, 'store']);
    Route::get('/permissions/{id}', [PermissionController::class, 'show']);
    Route::delete('/permissions/{id}', [PermissionController::class, 'destroy']);

    Route::post('/users/{id}/roles', [UserRoleController::class, 'assign']);
    Route::delete('/users/{id}/roles/{roleId}', [UserRoleController::class, 'revoke']);
});

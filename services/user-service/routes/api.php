<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AttachmentController;
use App\Http\Controllers\Api\V1\Internal\TenantController as InternalTenantController;
use App\Http\Controllers\Api\V1\Internal\UserController as InternalUserController;
use App\Http\Controllers\Api\V1\PermissionController;
use App\Http\Controllers\Api\V1\PolicyController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\TenantController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| User Service API Routes — versioned under /api/v1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // ──────────────────────────────────────────────
    // Health check (no auth required)
    // ──────────────────────────────────────────────
    Route::get('/health', static fn () => response()->json([
        'success' => true,
        'data'    => ['status' => 'ok', 'service' => 'user-service'],
        'meta'    => [],
        'errors'  => null,
        'message' => 'Service is healthy',
    ]));

    // ──────────────────────────────────────────────
    // Internal routes — Auth service calls these
    // Protected by service-to-service token
    // ──────────────────────────────────────────────
    Route::prefix('internal')->middleware('verify.service.token')->group(function () {
        // User internal endpoints
        Route::get('/users/by-email',        [InternalUserController::class, 'findByEmail']);
        Route::get('/users/by-external-id',  [InternalUserController::class, 'findByExternalId']);
        Route::post('/users/validate-credentials', [InternalUserController::class, 'validateCredentials']);
        Route::post('/users/login-event',    [InternalUserController::class, 'recordLoginEvent']);
        Route::get('/users/{userId}/claims', [InternalUserController::class, 'getUserClaims']);
        Route::post('/users/{userId}/increment-token-version', [InternalUserController::class, 'incrementTokenVersion']);
        Route::get('/users/{userId}',        [InternalUserController::class, 'findById']);

        // Tenant internal endpoints (IAM config & feature flags for auth-service)
        Route::get('/tenants/{tenantId}/iam-config',     [InternalTenantController::class, 'getIamConfig']);
        Route::get('/tenants/{tenantId}/feature-flags',  [InternalTenantController::class, 'getFeatureFlags']);
        Route::put('/tenants/{tenantId}/feature-flags',  [InternalTenantController::class, 'updateFeatureFlags']);
    });

    // ──────────────────────────────────────────────
    // Protected routes — require valid JWT
    // ──────────────────────────────────────────────
    Route::middleware('auth.jwt')->group(function () {

        // Users
        Route::get('/users/profile', [UserController::class, 'profile']);
        Route::post('/users/{id}/avatar', [UserController::class, 'uploadAvatar']);
        Route::apiResource('users', UserController::class);

        // Tenants — CRUD + hierarchy + runtime IAM config + feature flags
        Route::get('/tenants/{id}/hierarchy',      [TenantController::class, 'hierarchy']);
        Route::get('/tenants/{id}/iam-config',     [TenantController::class, 'getIamConfig']);
        Route::put('/tenants/{id}/iam-config',     [TenantController::class, 'updateIamConfig']);
        Route::get('/tenants/{id}/feature-flags',  [TenantController::class, 'getFeatureFlags']);
        Route::put('/tenants/{id}/feature-flags',  [TenantController::class, 'updateFeatureFlags']);
        Route::apiResource('tenants', TenantController::class);

        // Roles
        Route::post('/roles/assign',  [RoleController::class, 'assignToUser']);
        Route::post('/roles/revoke',  [RoleController::class, 'revokeFromUser']);
        Route::get('/roles/{id}/permissions',  [RoleController::class, 'permissions']);
        Route::put('/roles/{id}/permissions',  [RoleController::class, 'syncPermissions']);
        Route::apiResource('roles', RoleController::class);

        // Permissions
        Route::apiResource('permissions', PermissionController::class);

        // Attachments — general multi-file upload for any entity
        Route::get('/attachments',                  [AttachmentController::class, 'index']);
        Route::post('/attachments',                 [AttachmentController::class, 'upload']);
        Route::delete('/attachments/{id}',          [AttachmentController::class, 'destroy']);
        Route::get('/attachments/{id}/signed-url',  [AttachmentController::class, 'signedUrl']);

        // ABAC Policies
        Route::post('/policies/evaluate', [PolicyController::class, 'evaluate']);
        Route::apiResource('policies', PolicyController::class);
    });
});

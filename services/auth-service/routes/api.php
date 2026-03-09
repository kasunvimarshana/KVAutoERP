<?php

declare(strict_types=1);

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HealthController;
use App\Http\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth Service API Routes
|--------------------------------------------------------------------------
|
| All routes are prefixed with /api by bootstrap/app.php.
| The X-Tenant-ID header is required on all authenticated routes.
|
*/

// ── Health checks (no auth required) ─────────────────────────────────────
Route::prefix('health')->controller(HealthController::class)->group(function (): void {
    Route::get('/', 'index')->name('health.index');
    Route::get('/ping', 'ping')->name('health.ping');
});

// ── Public auth routes ────────────────────────────────────────────────────
Route::prefix('auth')->controller(AuthController::class)->group(function (): void {
    Route::post('/login', 'login')->name('auth.login');
    Route::post('/register', 'register')->name('auth.register');
    Route::post('/refresh', 'refresh')->name('auth.refresh');
});

// ── Protected auth routes (require valid Passport token) ─────────────────
Route::prefix('auth')
    ->middleware(Authenticate::class)
    ->controller(AuthController::class)
    ->group(function (): void {
        Route::post('/logout', 'logout')->name('auth.logout');
        Route::get('/me', 'me')->name('auth.me');
    });

// ── User management routes (admin only) ──────────────────────────────────
Route::middleware([Authenticate::class, 'can:is-admin'])
    ->controller(AuthController::class)
    ->group(function (): void {
        Route::get('/users', 'users')->name('users.index');
        Route::get('/users/{id}', function (string $id, \Illuminate\Http\Request $request) {
            /** @var \App\Services\AuthService $authService */
            $authService = app(\App\Services\AuthService::class);

            $result = $authService->getUser(
                userId: $id,
                tenantId: $request->header('X-Tenant-ID', ''),
            );

            return response()->json(['success' => true, 'message' => 'Success', 'data' => $result, 'meta' => [], 'errors' => []]);
        })->name('users.show');
    });

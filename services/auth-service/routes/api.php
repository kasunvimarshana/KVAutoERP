<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\SessionController;
use App\Http\Controllers\Api\V1\SsoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth Service API Routes — versioned under /api/v1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // ──────────────────────────────────────────────
    // Health check (no auth required)
    // ──────────────────────────────────────────────
    Route::get('/health', static fn () => response()->json([
        'success' => true,
        'data'    => ['status' => 'ok', 'service' => 'auth-service'],
        'meta'    => [],
        'errors'  => null,
        'message' => 'Service is healthy',
    ]));

    // ──────────────────────────────────────────────
    // Public auth endpoints
    // ──────────────────────────────────────────────
    Route::prefix('auth')->group(function () {

        // Login — throttled to prevent brute-force
        Route::post('/login',   [AuthController::class, 'login'])
            ->middleware('throttle:60,1');

        // Refresh — tighter limit since tokens are short-lived
        Route::post('/refresh', [AuthController::class, 'refresh'])
            ->middleware('throttle:30,1');

        // Service-to-service token issuance — internal services only
        Route::post('/service-token', [AuthController::class, 'serviceToken'])
            ->middleware('throttle:30,1');

        // Public key endpoint for downstream service verification
        Route::get('/public-key', [AuthController::class, 'publicKey']);

        // JWKS endpoint — standard JSON Web Key Set for token verification
        Route::get('/.well-known/jwks.json', [AuthController::class, 'jwks']);

        // ──────────────────────────────────────────
        // Protected auth endpoints (require valid JWT)
        // ──────────────────────────────────────────
        Route::middleware('auth.jwt')->group(function () {
            Route::post('/logout',          [AuthController::class, 'logout']);
            Route::get('/verify',           [AuthController::class, 'verify']);
            Route::get('/sessions',         [SessionController::class, 'devices']);
            Route::delete('/sessions/{deviceId}', [SessionController::class, 'revokeDevice']);
            Route::delete('/sessions',      [SessionController::class, 'revokeAll']);
        });
    });

    // ──────────────────────────────────────────────
    // SSO / Federated login endpoints
    // Session middleware is required for CSRF state management in the OAuth2
    // redirect/callback flow.
    // ──────────────────────────────────────────────
    Route::prefix('sso')->middleware(\Illuminate\Session\Middleware\StartSession::class)->group(function () {
        Route::get('/redirect',  [SsoController::class, 'redirect']);
        Route::get('/callback',  [SsoController::class, 'callback']);
        Route::get('/providers', [SsoController::class, 'providers']);
    });
});

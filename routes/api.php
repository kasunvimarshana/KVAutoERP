<?php

declare(strict_types=1);

use App\Core\Http\Middleware\TenantMiddleware;
use App\Modules\Auth\Http\Controllers\AuthController;
use App\Modules\Health\Http\Controllers\HealthController;
use App\Modules\Inventory\Http\Controllers\ProductController;
use App\Modules\Order\Http\Controllers\OrderController;
use App\Modules\Tenant\Http\Controllers\TenantController;
use App\Modules\Webhook\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes  –  REST API v1
|--------------------------------------------------------------------------
|
| Route structure:
|   /api/v1/health          – Health checks (public)
|   /api/v1/auth            – Authentication (public)
|   /api/v1/tenants         – Tenant management (super-admin)
|   /api/v1/inventory       – Inventory management (tenant-scoped)
|   /api/v1/orders          – Order management + Saga (tenant-scoped)
|   /api/v1/webhooks        – Webhook subscriptions (tenant-scoped)
|
*/

// =========================================================================
//  Health Checks  (no auth required)
// =========================================================================
Route::prefix('v1/health')->group(function (): void {
    Route::get('/',      [HealthController::class, 'check'])->name('health.check');
    Route::get('/live',  [HealthController::class, 'live'])->name('health.live');
    Route::get('/ready', [HealthController::class, 'ready'])->name('health.ready');
});

// =========================================================================
//  Authentication  (public)
// =========================================================================
Route::prefix('v1/auth')->group(function (): void {
    Route::post('/login',    [AuthController::class, 'login'])->name('auth.login');
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register');

    Route::middleware('auth:api')->group(function (): void {
        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/me',      [AuthController::class, 'me'])->name('auth.me');
    });
});

// =========================================================================
//  Tenant Management  (super-admin only)
// =========================================================================
Route::prefix('v1/tenants')
    ->middleware(['auth:api', 'role:super-admin'])
    ->group(function (): void {
        Route::get('/',         [TenantController::class, 'index'])->name('tenants.index');
        Route::post('/',        [TenantController::class, 'store'])->name('tenants.store');
        Route::get('/{id}',     [TenantController::class, 'show'])->name('tenants.show');
        Route::put('/{id}',     [TenantController::class, 'update'])->name('tenants.update');
        Route::delete('/{id}',  [TenantController::class, 'destroy'])->name('tenants.destroy');
    });

// =========================================================================
//  Tenant-Scoped API Routes
// =========================================================================
Route::prefix('v1')
    ->middleware(['auth:api', TenantMiddleware::class])
    ->group(function (): void {

        // ------------------------------------------------------------------
        //  Inventory
        // ------------------------------------------------------------------
        Route::prefix('inventory')->group(function (): void {
            Route::get('/products',           [ProductController::class, 'index'])->name('inventory.index');
            Route::get('/products/search',    [ProductController::class, 'search'])->name('inventory.search');
            Route::get('/products/low-stock', [ProductController::class, 'lowStock'])->name('inventory.low-stock');
            Route::get('/products/{id}',      [ProductController::class, 'show'])->name('inventory.show');
            Route::post('/products',          [ProductController::class, 'store'])->name('inventory.store');
            Route::put('/products/{id}',      [ProductController::class, 'update'])->name('inventory.update');
            Route::delete('/products/{id}',   [ProductController::class, 'destroy'])->name('inventory.destroy');
        });

        // ------------------------------------------------------------------
        //  Orders  (Saga orchestration)
        // ------------------------------------------------------------------
        Route::prefix('orders')->group(function (): void {
            Route::get('/',              [OrderController::class, 'index'])->name('orders.index');
            Route::post('/',             [OrderController::class, 'store'])->name('orders.store');
            Route::get('/{id}',          [OrderController::class, 'show'])->name('orders.show');
            Route::post('/{id}/cancel',  [OrderController::class, 'cancel'])->name('orders.cancel');
        });

        // ------------------------------------------------------------------
        //  Webhooks
        // ------------------------------------------------------------------
        Route::prefix('webhooks')->group(function (): void {
            Route::get('/',         [WebhookController::class, 'index'])->name('webhooks.index');
            Route::post('/',        [WebhookController::class, 'store'])->name('webhooks.store');
            Route::put('/{id}',     [WebhookController::class, 'update'])->name('webhooks.update');
            Route::delete('/{id}',  [WebhookController::class, 'destroy'])->name('webhooks.destroy');
        });
    });

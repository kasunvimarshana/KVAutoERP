<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HealthController;
use App\Http\Middleware\AuthenticateTenant;
use App\Http\Middleware\TenantMiddleware;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth Service API Routes
|--------------------------------------------------------------------------
*/

Route::get('/health', [HealthController::class, 'health'])->name('health');

Route::prefix('auth')->middleware([TenantMiddleware::class])->group(function (): void {

    // Public endpoints – tenant context resolved from header/body/subdomain
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('/login',    [AuthController::class, 'login'])->name('auth.login');
    Route::post('/refresh',  [AuthController::class, 'refresh'])->name('auth.refresh');

    // Protected endpoints – require valid Passport token AND matching tenant
    Route::middleware(['auth:api', AuthenticateTenant::class])->group(function (): void {
        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/me',      [AuthController::class, 'me'])->name('auth.me');
    });
});

<?php

use App\Http\Controllers\GatewayController;
use App\Http\Controllers\HealthCheckController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes – API Gateway
|--------------------------------------------------------------------------
*/

// Gateway self health-check
Route::get('/health',          [HealthCheckController::class, 'check']);
Route::get('/health/services', [HealthCheckController::class, 'checkAll']);

// All other requests are proxied to downstream services.
// Rate limiting is applied via the middleware configured in bootstrap/app.php.
Route::middleware(['throttle:api'])->group(function () {
    Route::any('/{service}/{path?}', [GatewayController::class, 'proxy'])
        ->where('path', '.*');
});

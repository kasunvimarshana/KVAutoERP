<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Configuration\Infrastructure\Http\Controllers\OrgUnitController;
use Modules\Configuration\Infrastructure\Http\Controllers\SystemConfigController;

Route::prefix('api')->middleware(['api'])->group(function () {
    Route::get('/org-units/tree', [OrgUnitController::class, 'tree']);
    Route::get('/org-units', [OrgUnitController::class, 'index']);
    Route::post('/org-units', [OrgUnitController::class, 'store']);
    Route::get('/org-units/{id}', [OrgUnitController::class, 'show']);
    Route::put('/org-units/{id}', [OrgUnitController::class, 'update']);
    Route::delete('/org-units/{id}', [OrgUnitController::class, 'destroy']);
    Route::post('/org-units/{id}/move', [OrgUnitController::class, 'move']);

    Route::get('/system-configs', [SystemConfigController::class, 'index']);
    Route::get('/system-configs/{id}', [SystemConfigController::class, 'show']);
    Route::post('/system-configs', [SystemConfigController::class, 'upsert']);
    Route::put('/system-configs/{id}', [SystemConfigController::class, 'upsert']);
    Route::delete('/system-configs/{id}', [SystemConfigController::class, 'destroy']);
});

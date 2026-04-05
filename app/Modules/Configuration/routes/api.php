<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('api')->middleware('auth:api')->group(function () {
    Route::apiResource('settings', \Modules\Configuration\Infrastructure\Http\Controllers\SettingController::class);
    Route::apiResource('org-units', \Modules\Configuration\Infrastructure\Http\Controllers\OrgUnitController::class);
    Route::post('org-units/{id}/move', [\Modules\Configuration\Infrastructure\Http\Controllers\OrgUnitController::class, 'move']);
    Route::get('org-units/{id}/descendants', [\Modules\Configuration\Infrastructure\Http\Controllers\OrgUnitController::class, 'descendants']);
    Route::get('org-units/{id}/ancestors', [\Modules\Configuration\Infrastructure\Http\Controllers\OrgUnitController::class, 'ancestors']);
});

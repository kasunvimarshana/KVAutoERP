<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Configuration\Infrastructure\Http\Controllers\OrgUnitController;
use Modules\Configuration\Infrastructure\Http\Controllers\SettingController;

Route::prefix('api')->group(function () {
    Route::get('/org-units/tree', [OrgUnitController::class, 'tree']);
    Route::apiResource('/org-units', OrgUnitController::class)->only(['index', 'store', 'show', 'update', 'destroy']);

    Route::get('/settings', [SettingController::class, 'index']);
    Route::get('/settings/{group}', [SettingController::class, 'group']);
    Route::get('/settings/{group}/{key}', [SettingController::class, 'show']);
    Route::put('/settings/{group}/{key}', [SettingController::class, 'update']);
});

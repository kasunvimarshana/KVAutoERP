<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Tax\Infrastructure\Http\Controllers\TaxGroupController;
use Modules\Tax\Infrastructure\Http\Controllers\TaxGroupRateController;
use Modules\Tax\Infrastructure\Http\Controllers\TaxRateController;

Route::prefix('api')->middleware('auth:api')->group(function () {
    Route::apiResource('tax-rates', TaxRateController::class);
    Route::apiResource('tax-groups', TaxGroupController::class);
    Route::get('tax-group-rates', [TaxGroupRateController::class, 'index']);
    Route::post('tax-group-rates', [TaxGroupRateController::class, 'store']);
    Route::delete('tax-group-rates/{id}', [TaxGroupRateController::class, 'destroy']);
    Route::post('tax/calculate', [TaxGroupController::class, 'calculate']);
});

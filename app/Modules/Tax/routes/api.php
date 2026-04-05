<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Tax\Infrastructure\Http\Controllers\TaxGroupController;

Route::prefix('api')->group(function () {
    Route::apiResource('tax-groups', TaxGroupController::class);
    Route::post('tax-groups/{taxGroupId}/rates', [TaxGroupController::class, 'addRate']);
    Route::delete('tax-groups/rates/{id}', [TaxGroupController::class, 'removeRate']);
    Route::post('tax-groups/{id}/calculate', [TaxGroupController::class, 'calculate']);
});

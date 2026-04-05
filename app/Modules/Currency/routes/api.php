<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Currency\Infrastructure\Http\Controllers\CurrencyController;
use Modules\Currency\Infrastructure\Http\Controllers\ExchangeRateController;

Route::prefix('api')->group(function () {
    Route::apiResource('currencies', CurrencyController::class);
    Route::patch('currencies/{id}/set-default', [CurrencyController::class, 'setDefault']);

    Route::apiResource('exchange-rates', ExchangeRateController::class)->only(['index', 'store']);
    Route::post('exchange-rates/convert', [ExchangeRateController::class, 'convert']);
});

<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Pricing\Infrastructure\Http\Controllers\DiscountController;
use Modules\Pricing\Infrastructure\Http\Controllers\PriceListController;
use Modules\Pricing\Infrastructure\Http\Controllers\PriceListItemController;

Route::prefix('api')->middleware('auth:api')->group(function () {
    Route::apiResource('price-lists', PriceListController::class);
    Route::apiResource('price-list-items', PriceListItemController::class);
    Route::apiResource('discounts', DiscountController::class);
    Route::post('discounts/apply', [DiscountController::class, 'apply']);
});

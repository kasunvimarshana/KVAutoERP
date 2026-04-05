<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Barcode\Infrastructure\Http\Controllers\BarcodeController;
use Modules\Barcode\Infrastructure\Http\Controllers\BarcodePrintJobController;
use Modules\Barcode\Infrastructure\Http\Controllers\LabelTemplateController;

Route::prefix('api')->group(function () {
    Route::post('barcodes/generate', [BarcodeController::class, 'store']);
    Route::post('barcodes/scan', [BarcodeController::class, 'scan']);

    Route::apiResource('label-templates', LabelTemplateController::class);
    Route::post('label-templates/{id}/render', [LabelTemplateController::class, 'render']);

    Route::apiResource('barcode-print-jobs', BarcodePrintJobController::class)->only(['index', 'store', 'show']);
    Route::post('barcode-print-jobs/{id}/dispatch', [BarcodePrintJobController::class, 'dispatch']);
});

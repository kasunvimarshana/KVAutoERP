<?php

use Illuminate\Support\Facades\Route;
use Modules\Barcode\Infrastructure\Http\Controllers\BarcodeDefinitionController;
use Modules\Barcode\Infrastructure\Http\Controllers\BarcodeScanController;

Route::prefix('api')->group(function () {

    // ── Barcode Definitions ───────────────────────────────────────────────────
    Route::get('barcodes',                          [BarcodeDefinitionController::class, 'index']);
    Route::post('barcodes',                         [BarcodeDefinitionController::class, 'store']);
    Route::get('barcodes/{id}',                     [BarcodeDefinitionController::class, 'show']);
    Route::get('barcodes/{id}/generate',            [BarcodeDefinitionController::class, 'generate']);
    Route::patch('barcodes/{id}/activate',          [BarcodeDefinitionController::class, 'activate']);
    Route::patch('barcodes/{id}/deactivate',        [BarcodeDefinitionController::class, 'deactivate']);
    Route::delete('barcodes/{id}',                  [BarcodeDefinitionController::class, 'destroy']);

    // ── Barcode Scans ─────────────────────────────────────────────────────────
    Route::get('barcode-scans',                     [BarcodeScanController::class, 'index']);
    Route::post('barcode-scans',                    [BarcodeScanController::class, 'store']);
    Route::get('barcode-scans/{id}',                [BarcodeScanController::class, 'show']);
    Route::delete('barcode-scans/{id}',             [BarcodeScanController::class, 'destroy']);
});

<?php
use Illuminate\Support\Facades\Route;
use Modules\Brand\Infrastructure\Http\Controllers\BrandController;
Route::apiResource('brands', BrandController::class);

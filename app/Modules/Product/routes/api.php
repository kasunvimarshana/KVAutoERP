<?php
use Illuminate\Support\Facades\Route;
use Modules\Product\Infrastructure\Http\Controllers\ProductController;
Route::apiResource('products', ProductController::class);

<?php
use Illuminate\Support\Facades\Route;
use Modules\Category\Infrastructure\Http\Controllers\CategoryController;
use Modules\Category\Infrastructure\Http\Controllers\CategoryImageController;

Route::apiResource('categories', CategoryController::class);
Route::post('categories/{category}/images', [CategoryImageController::class, 'store']);
Route::delete('categories/{category}/images/{image}', [CategoryImageController::class, 'destroy']);

<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use LaravelDDD\Examples\Product\Presentation\Http\Controllers\ProductController;

/*
|--------------------------------------------------------------------------
| Product Context API Routes
|--------------------------------------------------------------------------
|
| These routes expose the Product bounded context via a REST API.
| Register this file in your application's RouteServiceProvider.
|
*/

Route::prefix('products')->group(function (): void {
    Route::get('/', [ProductController::class, 'index']);
    Route::post('/', [ProductController::class, 'store']);
    Route::get('/{id}', [ProductController::class, 'show']);
});

<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\UomController;
use Illuminate\Support\Facades\Route;
use KvEnterprise\SharedKernel\Http\Middleware\TenantContextMiddleware;

/*
|--------------------------------------------------------------------------
| Product Service API Routes — v1
|--------------------------------------------------------------------------
|
| All routes are protected by JWT verification middleware followed by
| the tenant context middleware.  The tenant_id is extracted from the
| verified JWT claims and bound into the service container before any
| controller action runs.
|
*/

Route::prefix('v1')
    ->middleware(['jwt.verify', TenantContextMiddleware::class])
    ->group(function (): void {

        /*
        |--------------------------------------------------------------
        | Product Categories
        |--------------------------------------------------------------
        */
        // Read access: any authenticated user.
        Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('categories/{category}', [CategoryController::class, 'show'])->name('categories.show');

        // Write access: requires products.manage permission.
        Route::middleware('require.permission:products.manage')->group(function (): void {
            Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
            Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
            Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        });

        /*
        |--------------------------------------------------------------
        | Units of Measure
        |--------------------------------------------------------------
        */
        Route::post('uom/convert', [UomController::class, 'convert'])
            ->name('uom.convert');

        // Read access: any authenticated user.
        Route::get('uom', [UomController::class, 'index'])->name('uom.index');
        Route::get('uom/{uom}', [UomController::class, 'show'])->name('uom.show');
        Route::get('uom/{uom}/conversions', [UomController::class, 'indexConversions'])
            ->name('uom.conversions.index');

        // Write access: requires products.manage permission.
        Route::middleware('require.permission:products.manage')->group(function (): void {
            Route::post('uom', [UomController::class, 'store'])->name('uom.store');
            Route::put('uom/{uom}', [UomController::class, 'update'])->name('uom.update');
            Route::delete('uom/{uom}', [UomController::class, 'destroy'])->name('uom.destroy');
            Route::post('uom/{uom}/conversions', [UomController::class, 'storeConversion'])
                ->name('uom.conversions.store');
        });

        /*
        |--------------------------------------------------------------
        | Products
        |--------------------------------------------------------------
        */
        Route::prefix('products')->group(function (): void {
            // Read access: any authenticated user.
            Route::get('/', [ProductController::class, 'index'])->name('products.index');
            Route::get('/{product}', [ProductController::class, 'show'])->name('products.show');
            Route::get('/{product}/prices', [ProductController::class, 'getPrices'])->name('products.prices.index');
            Route::get('/{product}/variants', [ProductController::class, 'getVariants'])->name('products.variants.index');

            // Write access: requires products.manage permission.
            Route::middleware('require.permission:products.manage')->group(function (): void {
                Route::post('/', [ProductController::class, 'store'])->name('products.store');
                Route::put('/{product}', [ProductController::class, 'update'])->name('products.update');
                Route::delete('/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
                Route::post('/{product}/prices', [ProductController::class, 'addPrice'])->name('products.prices.store');
                Route::post('/{product}/variants', [ProductController::class, 'addVariant'])->name('products.variants.store');
            });
        });
    });

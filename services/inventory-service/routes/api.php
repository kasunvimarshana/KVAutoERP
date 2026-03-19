<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\StockController;
use App\Http\Controllers\Api\V1\WarehouseController;
use Illuminate\Support\Facades\Route;
use KvEnterprise\SharedKernel\Http\Middleware\TenantContextMiddleware;

/*
|--------------------------------------------------------------------------
| Inventory Service API Routes — v1
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
        | Warehouses and Bins
        |--------------------------------------------------------------
        */
        // Read access: any authenticated user.
        Route::get('warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
        Route::get('warehouses/{warehouse}', [WarehouseController::class, 'show'])->name('warehouses.show');
        Route::get('warehouses/{warehouse}/bins', [WarehouseController::class, 'indexBins'])
            ->name('warehouses.bins.index');

        // Write access: requires warehouses.manage permission.
        Route::middleware('require.permission:warehouses.manage')->group(function (): void {
            Route::post('warehouses', [WarehouseController::class, 'store'])->name('warehouses.store');
            Route::put('warehouses/{warehouse}', [WarehouseController::class, 'update'])->name('warehouses.update');
            Route::delete('warehouses/{warehouse}', [WarehouseController::class, 'destroy'])->name('warehouses.destroy');
            Route::post('warehouses/{warehouse}/bins', [WarehouseController::class, 'storeBin'])
                ->name('warehouses.bins.store');
            Route::put('warehouses/{warehouse}/bins/{bin}', [WarehouseController::class, 'updateBin'])
                ->name('warehouses.bins.update');
            Route::delete('warehouses/{warehouse}/bins/{bin}', [WarehouseController::class, 'destroyBin'])
                ->name('warehouses.bins.destroy');
        });

        /*
        |--------------------------------------------------------------
        | Stock Levels (read-only queries on current stock)
        |--------------------------------------------------------------
        */
        Route::get('stock', [StockController::class, 'index'])
            ->name('stock.index');
        Route::get('stock/{productId}', [StockController::class, 'show'])
            ->name('stock.show');

        /*
        |--------------------------------------------------------------
        | Stock Movements (ledger-based mutations) — require inventory.manage
        |--------------------------------------------------------------
        */
        Route::middleware('require.permission:inventory.manage')->group(function (): void {
            Route::post('stock/receive', [StockController::class, 'receive'])
                ->name('stock.receive');
            Route::post('stock/dispatch', [StockController::class, 'dispatch'])
                ->name('stock.dispatch');
            Route::post('stock/adjust', [StockController::class, 'adjust'])
                ->name('stock.adjust');
            Route::post('stock/transfer', [StockController::class, 'transfer'])
                ->name('stock.transfer');
        });

        /*
        |--------------------------------------------------------------
        | Stock Reservations
        |--------------------------------------------------------------
        */
        Route::get('reservations', [StockController::class, 'indexReservations'])
            ->name('reservations.index');
        Route::middleware('require.permission:inventory.reserve')->group(function (): void {
            Route::post('reservations', [StockController::class, 'reserve'])
                ->name('reservations.store');
            Route::delete('reservations/{reservation}', [StockController::class, 'releaseReservation'])
                ->name('reservations.destroy');
        });

        /*
        |--------------------------------------------------------------
        | Stock Ledger History
        |--------------------------------------------------------------
        */
        Route::get('ledger', [StockController::class, 'ledger'])
            ->name('ledger.index');

        /*
        |--------------------------------------------------------------
        | Reorder Rules
        |--------------------------------------------------------------
        */
        Route::get('reorder-rules', [StockController::class, 'indexReorderRules'])
            ->name('reorder-rules.index');
        Route::middleware('require.permission:inventory.manage')->group(function (): void {
            Route::post('reorder-rules', [StockController::class, 'storeReorderRule'])
                ->name('reorder-rules.store');
            Route::put('reorder-rules/{rule}', [StockController::class, 'updateReorderRule'])
                ->name('reorder-rules.update');
            Route::delete('reorder-rules/{rule}', [StockController::class, 'destroyReorderRule'])
                ->name('reorder-rules.destroy');
        });

        /*
        |--------------------------------------------------------------
        | Cycle Counts
        |--------------------------------------------------------------
        */
        Route::get('cycle-counts', [StockController::class, 'indexCycleCounts'])
            ->name('cycle-counts.index');
        Route::get('cycle-counts/{count}', [StockController::class, 'showCycleCount'])
            ->name('cycle-counts.show');
        Route::middleware('require.permission:inventory.manage')->group(function (): void {
            Route::post('cycle-counts', [StockController::class, 'storeCycleCount'])
                ->name('cycle-counts.store');
            Route::post('cycle-counts/{count}/confirm', [StockController::class, 'confirmCycleCount'])
                ->name('cycle-counts.confirm');
        });
    });

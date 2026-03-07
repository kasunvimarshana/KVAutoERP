<?php
use App\Http\Controllers\HealthController;
use App\Http\Controllers\InventoryController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class);

Route::middleware(['App\Middleware\TenantMiddleware', 'auth:api'])->group(function (): void {
    Route::apiResource('inventory', InventoryController::class);
    Route::post('inventory/{id}/adjust', [InventoryController::class, 'adjustStock']);
    Route::get('inventory/product/{productId}', [InventoryController::class, 'getByProduct']);
    Route::get('inventory/with-products', [InventoryController::class, 'listWithProductDetails']);
    Route::get('inventory/filter-by-product', [InventoryController::class, 'filterByProductName']);
});

Route::middleware(['App\Middleware\TenantMiddleware', 'App\Middleware\VerifyServiceToken'])->group(function (): void {
    Route::get('/internal/inventory', [InventoryController::class, 'index']);
    Route::post('/webhooks/receive', function (\Illuminate\Http\Request $request) {
        \Illuminate\Support\Facades\Log::info('Webhook received', $request->all());
        return response()->json(['success' => true, 'message' => 'Webhook received.']);
    });
});

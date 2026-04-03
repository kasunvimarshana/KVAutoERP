<?php
use Illuminate\Support\Facades\Route;
use Modules\Tenant\Infrastructure\Http\Controllers\TenantAttachmentController;
use Modules\Tenant\Infrastructure\Http\Controllers\TenantController;

Route::prefix('tenants')->group(function () {
    Route::apiResource('', TenantController::class)->parameters(['' => 'tenant']);
    Route::post('{tenant}/attachments/bulk', [TenantAttachmentController::class, 'storeMany']);
    Route::post('{tenant}/attachments', [TenantAttachmentController::class, 'store']);
    Route::delete('{tenant}/attachments/{attachment}', [TenantAttachmentController::class, 'destroy']);
    Route::get('{tenant}/attachments', [TenantAttachmentController::class, 'index']);
});

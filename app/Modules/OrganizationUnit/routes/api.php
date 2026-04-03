<?php
use Illuminate\Support\Facades\Route;
use Modules\OrganizationUnit\Infrastructure\Http\Controllers\OrganizationUnitAttachmentController;
use Modules\OrganizationUnit\Infrastructure\Http\Controllers\OrganizationUnitController;

Route::get('org-units/tree', [OrganizationUnitController::class, 'index'])->name('tree');
Route::get('org-units/{unit}/descendants', [OrganizationUnitController::class, 'descendants']);
Route::get('org-units/{unit}/ancestors', [OrganizationUnitController::class, 'ancestors']);
Route::post('org-units/{unit}/attachments/bulk', [OrganizationUnitAttachmentController::class, 'storeMany']);
Route::post('org-units/{unit}/attachments', [OrganizationUnitAttachmentController::class, 'store']);
Route::delete('org-units/{unit}/attachments/{attachment}', [OrganizationUnitAttachmentController::class, 'destroy']);
Route::patch('org-units/{unit}/attachments/{attachment}', [OrganizationUnitAttachmentController::class, 'update']);
Route::post('org-units/{unit}/attachments/{attachment}/replace', [OrganizationUnitAttachmentController::class, 'replace']);
Route::get('org-units/{unit}/attachments', [OrganizationUnitAttachmentController::class, 'index']);
Route::apiResource('org-units', OrganizationUnitController::class);

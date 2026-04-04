<?php
use Illuminate\Support\Facades\Route;
use Modules\Accounting\Infrastructure\Http\Controllers\AccountController;
use Modules\Accounting\Infrastructure\Http\Controllers\JournalEntryController;
Route::prefix('api')->group(function () {
    Route::apiResource('accounts', AccountController::class);
    Route::get('journal-entries', [JournalEntryController::class, 'index']);
    Route::post('journal-entries', [JournalEntryController::class, 'store']);
    Route::get('journal-entries/{id}', [JournalEntryController::class, 'show']);
    Route::post('journal-entries/{id}/post', [JournalEntryController::class, 'post']);
    Route::delete('journal-entries/{id}', [JournalEntryController::class, 'destroy']);
});

<?php

use App\Modules\User\Controllers\UserController;
use App\Modules\User\Webhooks\UserWebhookHandler;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth.keycloak', 'tenant'])->prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::post('/', [UserController::class, 'store']);
    Route::get('/{id}', [UserController::class, 'show']);
    Route::put('/{id}', [UserController::class, 'update']);
    Route::delete('/{id}', [UserController::class, 'destroy']);
    Route::post('/{id}/restore', [UserController::class, 'restore']);
});

Route::middleware(['verify.service'])->prefix('webhooks/users')->group(function () {
    Route::post('/', [UserWebhookHandler::class, 'handle']);
});

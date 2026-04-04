<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\User\Infrastructure\Http\Controllers\ProfileController;
use Modules\User\Infrastructure\Http\Controllers\UserController;

Route::prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::post('/', [UserController::class, 'store']);
    Route::get('/{id}', [UserController::class, 'show']);
    Route::patch('/{id}', [UserController::class, 'update']);
    Route::delete('/{id}', [UserController::class, 'destroy']);
});

Route::prefix('profile')->group(function () {
    Route::get('/', [ProfileController::class, 'show']);
    Route::patch('/', [ProfileController::class, 'update']);
    Route::post('/change-password', [ProfileController::class, 'changePassword']);
    Route::patch('/preferences', [ProfileController::class, 'updatePreferences']);
    Route::post('/avatar', [ProfileController::class, 'uploadAvatar']);
});

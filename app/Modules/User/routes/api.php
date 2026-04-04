<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\User\Infrastructure\Http\Controllers\ProfileController;
use Modules\User\Infrastructure\Http\Controllers\UserController;

Route::prefix('api')->middleware(['api'])->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);

    Route::get('/profile', [ProfileController::class, 'show']);
    Route::patch('/profile', [ProfileController::class, 'update']);
    Route::post('/profile/change-password', [ProfileController::class, 'changePassword']);
    Route::post('/profile/avatar', [ProfileController::class, 'avatar']);
});

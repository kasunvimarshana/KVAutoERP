<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HealthController;

Route::get('/health', [HealthController::class, 'check']);

Route::prefix('users')->group(function () {
    Route::get('/',         [UserController::class, 'index']);
    Route::post('/',        [UserController::class, 'store']);
    Route::get('/{id}',     [UserController::class, 'show']);
    Route::put('/{id}',     [UserController::class, 'update']);
    Route::delete('/{id}',  [UserController::class, 'destroy']);
    Route::patch('/{id}/status', [UserController::class, 'updateStatus']);
});

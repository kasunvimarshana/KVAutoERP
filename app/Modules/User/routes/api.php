<?php
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::get('profile', [\Modules\User\Infrastructure\Http\Controllers\ProfileController::class, 'show']);
    Route::put('profile', [\Modules\User\Infrastructure\Http\Controllers\ProfileController::class, 'update']);
});

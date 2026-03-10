<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn() => response()->json(['service' => 'Auth Service', 'version' => '1.0.0']));

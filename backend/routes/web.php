<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json(['service' => 'SaaS Inventory Management API', 'version' => '1.0']));

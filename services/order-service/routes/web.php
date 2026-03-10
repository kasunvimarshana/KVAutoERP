<?php
use Illuminate\Support\Facades\Route;
Route::get('/', fn() => response()->json(['service' => 'Order Service', 'version' => '1.0.0']));

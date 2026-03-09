<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Laravel 10 bootstrap. Binds the HTTP and Console kernels.
|
*/

$app = new Application(dirname(__DIR__));

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class,
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class,
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class,
);

return $app;

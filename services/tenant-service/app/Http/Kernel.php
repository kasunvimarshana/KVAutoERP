<?php

declare(strict_types=1);

namespace App\Http;

use App\Http\Middleware\TenantContextMiddleware;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Middleware\SetCacheHeaders;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;

class Kernel extends HttpKernel
{
    /** @var list<class-string|string> */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \Illuminate\Foundation\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        HandleCors::class,
    ];

    /** @var array<string, list<class-string|string>> */
    protected $middlewareGroups = [
        'web' => [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            SubstituteBindings::class,
        ],

        'api' => [
            ThrottleRequests::class . ':api',
            SubstituteBindings::class,
        ],
    ];

    /** @var array<string, class-string> */
    protected $middlewareAliases = [
        'auth'          => Authenticate::class,
        'tenant'        => TenantContextMiddleware::class,
        'throttle'      => ThrottleRequests::class,
        'cache.headers' => SetCacheHeaders::class,
    ];
}

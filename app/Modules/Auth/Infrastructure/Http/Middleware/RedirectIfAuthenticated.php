<?php
declare(strict_types=1);
namespace Modules\Auth\Infrastructure\Http\Middleware;
use Closure;
use Illuminate\Http\Request;

class RedirectIfAuthenticated {
    public function handle(Request $request, Closure $next, string ...$guards): mixed {
        return $next($request);
    }
}

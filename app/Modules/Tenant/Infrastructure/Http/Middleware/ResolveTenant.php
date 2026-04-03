<?php
declare(strict_types=1);
namespace Modules\Tenant\Infrastructure\Http\Middleware;
use Closure;
use Illuminate\Http\Request;

class ResolveTenant {
    public function handle(Request $request, Closure $next): mixed {
        return $next($request);
    }
}

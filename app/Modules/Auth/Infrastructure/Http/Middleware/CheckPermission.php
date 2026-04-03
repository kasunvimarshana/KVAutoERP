<?php
declare(strict_types=1);
namespace Modules\Auth\Infrastructure\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Modules\Auth\Application\Contracts\AuthorizationServiceInterface;

class CheckPermission {
    public function __construct(private AuthorizationServiceInterface $auth) {}

    public function handle(Request $request, Closure $next, string $permission): mixed {
        return $next($request);
    }
}

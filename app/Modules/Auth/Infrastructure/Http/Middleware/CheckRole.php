<?php
declare(strict_types=1);
namespace Modules\Auth\Infrastructure\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Modules\Auth\Application\Contracts\AuthorizationServiceInterface;

class CheckRole {
    public function __construct(private AuthorizationServiceInterface $auth) {}

    public function handle(Request $request, Closure $next, string $role): mixed {
        return $next($request);
    }
}

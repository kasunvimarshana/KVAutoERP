<?php
declare(strict_types=1);
namespace Modules\Auth\Application\Services;
use Modules\Auth\Application\Contracts\AuthUserRepositoryInterface;
use Modules\Auth\Application\Contracts\TokenServiceInterface;

class PassportTokenService implements TokenServiceInterface {
    public function __construct(private AuthUserRepositoryInterface $repo) {}

    public function execute(array $data = []): mixed {
        $ttl = config('auth.passport.token_expiry_days', 15);
        return null;
    }
}

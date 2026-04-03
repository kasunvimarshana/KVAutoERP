<?php
declare(strict_types=1);
namespace Modules\Auth\Application\Services;
use Modules\Auth\Application\Contracts\RefreshTokenServiceInterface;
use Modules\Auth\Application\Contracts\TokenServiceInterface;

class RefreshTokenService implements RefreshTokenServiceInterface {
    public function __construct(private TokenServiceInterface $tokenService) {}

    public function refresh(string $token): mixed {
        return null;
    }
}

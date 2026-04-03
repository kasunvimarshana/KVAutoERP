<?php
declare(strict_types=1);
namespace Modules\Auth\Application\UseCases;
use Modules\Auth\Application\Contracts\RefreshTokenServiceInterface;

class RefreshToken {
    public function __construct(private RefreshTokenServiceInterface $service) {}

    public function execute(string $token): mixed {
        return $this->service->refresh($token);
    }
}

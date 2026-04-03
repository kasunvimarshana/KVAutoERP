<?php
declare(strict_types=1);
namespace Modules\Auth\Application\Services;
use Modules\Auth\Application\Contracts\SsoServiceInterface;
use Modules\Auth\Application\Contracts\TokenServiceInterface;

class SsoService implements SsoServiceInterface {
    public function __construct(private TokenServiceInterface $tokenService) {}

    public function execute(array $data = []): mixed {
        return null;
    }
}

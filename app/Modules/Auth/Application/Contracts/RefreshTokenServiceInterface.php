<?php
declare(strict_types=1);
namespace Modules\Auth\Application\Contracts;

interface RefreshTokenServiceInterface {
    public function refresh(string $token): mixed;
}

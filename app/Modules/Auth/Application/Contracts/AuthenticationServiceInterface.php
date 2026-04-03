<?php
declare(strict_types=1);
namespace Modules\Auth\Application\Contracts;

interface AuthenticationServiceInterface {
    public function execute(array $data = []): mixed;
}

<?php
declare(strict_types=1);
namespace Modules\Auth\Application\Contracts;

interface ResetPasswordServiceInterface {
    public function reset(array $data): mixed;
}

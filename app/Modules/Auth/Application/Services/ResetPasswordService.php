<?php
declare(strict_types=1);
namespace Modules\Auth\Application\Services;
use Modules\Auth\Application\Contracts\ResetPasswordServiceInterface;

class ResetPasswordService implements ResetPasswordServiceInterface {
    public function reset(array $data): mixed {
        return null;
    }
}

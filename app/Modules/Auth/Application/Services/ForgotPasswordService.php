<?php
declare(strict_types=1);
namespace Modules\Auth\Application\Services;
use Modules\Auth\Application\Contracts\ForgotPasswordServiceInterface;

class ForgotPasswordService implements ForgotPasswordServiceInterface {
    public function sendResetLink(string $email): mixed {
        return null;
    }
}

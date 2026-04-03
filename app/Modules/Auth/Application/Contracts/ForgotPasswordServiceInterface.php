<?php
declare(strict_types=1);
namespace Modules\Auth\Application\Contracts;

interface ForgotPasswordServiceInterface {
    public function sendResetLink(string $email): mixed;
}

<?php
declare(strict_types=1);
namespace Modules\Auth\Application\UseCases;
use Modules\Auth\Application\Contracts\ForgotPasswordServiceInterface;

class ForgotPassword {
    public function __construct(private ForgotPasswordServiceInterface $service) {}

    public function execute(string $email): mixed {
        return $this->service->sendResetLink($email);
    }
}

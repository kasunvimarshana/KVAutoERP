<?php
declare(strict_types=1);
namespace Modules\Auth\Application\UseCases;
use Modules\Auth\Application\Contracts\ResetPasswordServiceInterface;

class ResetPassword {
    public function __construct(private ResetPasswordServiceInterface $service) {}

    public function execute(array $data): mixed {
        return $this->service->reset($data);
    }
}

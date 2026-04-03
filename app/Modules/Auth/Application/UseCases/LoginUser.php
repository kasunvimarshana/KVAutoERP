<?php
declare(strict_types=1);
namespace Modules\Auth\Application\UseCases;
use Modules\Auth\Application\Contracts\LoginServiceInterface;

class LoginUser {
    public function __construct(private LoginServiceInterface $service) {}

    public function execute(array $data = []): mixed {
        return $this->service->execute($data);
    }
}

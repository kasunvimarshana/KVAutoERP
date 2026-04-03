<?php
declare(strict_types=1);
namespace Modules\Auth\Application\UseCases;
use Modules\Auth\Application\Contracts\LogoutServiceInterface;

class LogoutUser {
    public function __construct(private LogoutServiceInterface $service) {}

    public function execute(array $data = []): mixed {
        return $this->service->execute($data);
    }
}

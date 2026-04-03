<?php
declare(strict_types=1);
namespace Modules\Auth\Application\Services;
use Modules\Auth\Application\Contracts\AuthUserRepositoryInterface;
use Modules\Auth\Application\Contracts\LogoutServiceInterface;

class LogoutService implements LogoutServiceInterface {
    public function __construct(private AuthUserRepositoryInterface $repo) {}

    public function execute(array $data = []): mixed {
        return null;
    }
}

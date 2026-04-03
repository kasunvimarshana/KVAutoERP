<?php
declare(strict_types=1);
namespace Modules\User\Application\Services;
use Modules\User\Application\Contracts\UpdateProfileServiceInterface;
use Modules\User\Domain\RepositoryInterfaces\UserRepositoryInterface;

class UpdateProfileService implements UpdateProfileServiceInterface {
    public function __construct(private UserRepositoryInterface $users) {}

    public function execute(array $data = []): mixed {
        return null;
    }
}

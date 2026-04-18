<?php

declare(strict_types=1);

namespace Modules\User\Application\Services;

use Illuminate\Support\Facades\Hash;
use Modules\Core\Application\Services\BaseService;
use Modules\User\Application\Contracts\SetUserPasswordServiceInterface;
use Modules\User\Domain\Exceptions\UserNotFoundException;
use Modules\User\Domain\RepositoryInterfaces\UserRepositoryInterface;

class SetUserPasswordService extends BaseService implements SetUserPasswordServiceInterface
{
    public function __construct(private readonly UserRepositoryInterface $userRepository)
    {
        parent::__construct($userRepository);
    }

    protected function handle(array $data): mixed
    {
        $userId = (int) $data['user_id'];
        $password = (string) $data['password'];

        $user = $this->userRepository->find($userId);
        if (! $user) {
            throw new UserNotFoundException($userId);
        }

        $this->userRepository->changePassword($userId, Hash::make($password));

        return null;
    }
}

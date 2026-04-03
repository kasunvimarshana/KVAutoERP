<?php

declare(strict_types=1);

namespace Modules\User\Application\Services;

use Illuminate\Support\Facades\Hash;
use Modules\Core\Application\Services\BaseService;
use Modules\User\Application\Contracts\ChangePasswordServiceInterface;
use Modules\User\Domain\Exceptions\UserNotFoundException;
use Modules\User\Domain\Events\UserPasswordChanged;
use Modules\User\Domain\RepositoryInterfaces\UserRepositoryInterface;
use Modules\Core\Domain\Exceptions\DomainException;

class ChangePasswordService extends BaseService implements ChangePasswordServiceInterface
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->userRepository = $repository;
    }

    protected function handle(array $data): mixed
    {
        $userId = $data['user_id'];
        $currentPassword = $data['current_password'];
        $newPassword = $data['password'];

        $user = $this->userRepository->find($userId);
        if (! $user) {
            throw new UserNotFoundException($userId);
        }

        // Verify current password against stored hash
        $model = \Modules\User\Infrastructure\Persistence\Eloquent\Models\UserModel::find($userId);
        if (! $model || ! Hash::check($currentPassword, $model->password)) {
            throw new DomainException('Current password is incorrect.');
        }

        $this->userRepository->changePassword($userId, Hash::make($newPassword));
        $this->addEvent(new UserPasswordChanged($user));

        return null;
    }
}

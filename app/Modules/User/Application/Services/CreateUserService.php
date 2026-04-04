<?php

declare(strict_types=1);

namespace Modules\User\Application\Services;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Modules\User\Application\Contracts\CreateUserServiceInterface;
use Modules\User\Application\DTOs\CreateUserData;
use Modules\User\Domain\Entities\User;
use Modules\User\Domain\Events\UserCreated;
use Modules\User\Domain\Exceptions\UserEmailAlreadyExistsException;
use Modules\User\Domain\RepositoryInterfaces\UserRepositoryInterface;

class CreateUserService implements CreateUserServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
    ) {}

    public function execute(CreateUserData $data): User
    {
        $existing = $this->repository->findByEmail($data->email);
        if ($existing !== null) {
            throw new UserEmailAlreadyExistsException($data->email);
        }

        $user = new User(
            id: null,
            tenantId: $data->tenantId,
            orgUnitId: $data->orgUnitId,
            name: $data->name,
            email: $data->email,
            password: Hash::make($data->password),
            avatar: null,
            phone: $data->phone,
            locale: $data->locale,
            timezone: $data->timezone,
            status: 'active',
            preferences: [],
            emailVerifiedAt: null,
            createdAt: null,
            updatedAt: null,
        );

        $saved = $this->repository->save($user);

        Event::dispatch(new UserCreated($saved->tenantId, $saved->id, $saved->orgUnitId));

        return $saved;
    }
}

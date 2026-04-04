<?php

declare(strict_types=1);

namespace Modules\User\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Modules\User\Application\Contracts\CreateUserServiceInterface;
use Modules\User\Application\DTOs\CreateUserData;
use Modules\User\Domain\Entities\User;
use Modules\User\Domain\Events\UserCreated;
use Modules\User\Domain\Exceptions\UserAlreadyExistsException;
use Modules\User\Domain\Repositories\UserRepositoryInterface;

class CreateUserService implements CreateUserServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
    ) {}

    public function execute(CreateUserData $data): User
    {
        return DB::transaction(function () use ($data): User {
            if ($this->repository->findByEmail($data->email) !== null) {
                throw new UserAlreadyExistsException($data->email);
            }

            $user = $this->repository->create([
                'tenant_id' => $data->tenantId,
                'name'      => $data->name,
                'email'     => $data->email,
                'password'  => Hash::make($data->password),
                'avatar'    => $data->avatar,
                'timezone'  => $data->timezone,
                'locale'    => $data->locale,
                'status'    => $data->status,
            ]);

            Event::dispatch(new UserCreated($user));

            return $user;
        });
    }
}

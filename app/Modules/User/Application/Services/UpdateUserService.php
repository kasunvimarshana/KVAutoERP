<?php

declare(strict_types=1);

namespace Modules\User\Application\Services;

use Illuminate\Support\Facades\Event;
use Modules\User\Application\Contracts\UpdateUserServiceInterface;
use Modules\User\Application\DTOs\UpdateUserData;
use Modules\User\Domain\Entities\User;
use Modules\User\Domain\Events\UserUpdated;
use Modules\User\Domain\Exceptions\UserNotFoundException;
use Modules\User\Domain\RepositoryInterfaces\UserRepositoryInterface;

class UpdateUserService implements UpdateUserServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
    ) {}

    public function execute(int $id, UpdateUserData $data): User
    {
        $user = $this->repository->findById($id);
        if ($user === null) {
            throw new UserNotFoundException($id);
        }

        if ($data->name !== null) {
            $user->name = $data->name;
        }
        if ($data->email !== null) {
            $user->email = $data->email;
        }
        if ($data->phone !== null) {
            $user->phone = $data->phone;
        }
        if ($data->locale !== null) {
            $user->locale = $data->locale;
        }
        if ($data->timezone !== null) {
            $user->timezone = $data->timezone;
        }
        if ($data->status !== null) {
            $user->status = $data->status;
        }

        $saved = $this->repository->save($user);

        Event::dispatch(new UserUpdated($saved->tenantId, $saved->id, $saved->orgUnitId));

        return $saved;
    }
}

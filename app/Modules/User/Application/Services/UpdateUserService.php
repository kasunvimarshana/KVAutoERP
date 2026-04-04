<?php

declare(strict_types=1);

namespace Modules\User\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\User\Application\Contracts\UpdateUserServiceInterface;
use Modules\User\Application\DTOs\UpdateUserData;
use Modules\User\Domain\Entities\User;
use Modules\User\Domain\Events\UserUpdated;
use Modules\User\Domain\Exceptions\UserNotFoundException;
use Modules\User\Domain\Repositories\UserRepositoryInterface;

class UpdateUserService implements UpdateUserServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
    ) {}

    public function execute(int $id, UpdateUserData $data): User
    {
        return DB::transaction(function () use ($id, $data): User {
            if ($this->repository->findById($id) === null) {
                throw new UserNotFoundException($id);
            }

            $payload = array_filter([
                'name'     => $data->name,
                'email'    => $data->email,
                'timezone' => $data->timezone,
                'locale'   => $data->locale,
                'status'   => $data->status,
            ], fn ($v) => $v !== null);

            $user = $this->repository->update($id, $payload);

            Event::dispatch(new UserUpdated($user));

            return $user;
        });
    }
}

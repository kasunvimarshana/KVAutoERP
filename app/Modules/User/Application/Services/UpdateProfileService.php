<?php

declare(strict_types=1);

namespace Modules\User\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\User\Application\Contracts\UpdateProfileServiceInterface;
use Modules\User\Application\DTOs\UpdateProfileData;
use Modules\User\Domain\Entities\User;
use Modules\User\Domain\Events\UserProfileUpdated;
use Modules\User\Domain\Exceptions\UserNotFoundException;
use Modules\User\Domain\Repositories\UserRepositoryInterface;

class UpdateProfileService implements UpdateProfileServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
    ) {}

    public function execute(int $id, UpdateProfileData $data): User
    {
        return DB::transaction(function () use ($id, $data): User {
            if ($this->repository->findById($id) === null) {
                throw new UserNotFoundException($id);
            }

            $payload = array_filter([
                'name'     => $data->name,
                'timezone' => $data->timezone,
                'locale'   => $data->locale,
            ], fn ($v) => $v !== null);

            $user = $this->repository->update($id, $payload);

            Event::dispatch(new UserProfileUpdated($user));

            return $user;
        });
    }
}

<?php

declare(strict_types=1);

namespace Modules\User\Application\Services;

use Illuminate\Support\Facades\Event;
use Modules\User\Application\Contracts\UpdateProfileServiceInterface;
use Modules\User\Application\DTOs\UpdateProfileData;
use Modules\User\Domain\Entities\User;
use Modules\User\Domain\Events\UserProfileUpdated;
use Modules\User\Domain\Exceptions\UserNotFoundException;
use Modules\User\Domain\RepositoryInterfaces\UserRepositoryInterface;

class UpdateProfileService implements UpdateProfileServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
    ) {}

    public function execute(int $userId, UpdateProfileData $data): User
    {
        $user = $this->repository->findById($userId);
        if ($user === null) {
            throw new UserNotFoundException($userId);
        }

        if ($data->name !== null) {
            $user->name = $data->name;
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
        if ($data->preferences !== null) {
            $user->preferences = $data->preferences;
        }

        $saved = $this->repository->save($user);

        Event::dispatch(new UserProfileUpdated($saved->tenantId, $saved->id, $saved->orgUnitId));

        return $saved;
    }
}

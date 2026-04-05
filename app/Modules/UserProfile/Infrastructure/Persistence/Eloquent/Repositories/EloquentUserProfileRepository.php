<?php

declare(strict_types=1);

namespace Modules\UserProfile\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\UserProfile\Domain\Entities\UserProfile;
use Modules\UserProfile\Domain\RepositoryInterfaces\UserProfileRepositoryInterface;
use Modules\UserProfile\Infrastructure\Persistence\Eloquent\Models\UserProfileModel;

final class EloquentUserProfileRepository implements UserProfileRepositoryInterface
{
    public function __construct(
        private readonly UserProfileModel $model,
    ) {}

    public function findById(int $id): ?UserProfile
    {
        $record = $this->model->newQuery()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByUserId(int $userId): ?UserProfile
    {
        $record = $this->model->newQuery()->where('user_id', $userId)->first();

        return $record ? $this->toEntity($record) : null;
    }

    public function create(array $data): UserProfile
    {
        $record = $this->model->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?UserProfile
    {
        $record = $this->model->newQuery()->find($id);

        if ($record === null) {
            return null;
        }

        $record->update($data);

        return $this->toEntity($record->fresh());
    }

    public function updateByUserId(int $userId, array $data): ?UserProfile
    {
        $record = $this->model->newQuery()->where('user_id', $userId)->first();

        if ($record === null) {
            return null;
        }

        $record->update($data);

        return $this->toEntity($record->fresh());
    }

    public function delete(int $id): bool
    {
        $record = $this->model->newQuery()->find($id);

        if ($record === null) {
            return false;
        }

        return (bool) $record->delete();
    }

    public function deleteByUserId(int $userId): bool
    {
        return (bool) $this->model->newQuery()->where('user_id', $userId)->delete();
    }

    private function toEntity(UserProfileModel $model): UserProfile
    {
        return new UserProfile(
            id: $model->id,
            userId: $model->user_id,
            avatar: $model->avatar,
            bio: $model->bio,
            phone: $model->phone,
            address: $model->address,
            preferences: $model->preferences,
            timezone: $model->timezone,
            locale: $model->locale,
            createdAt: \DateTimeImmutable::createFromMutable($model->created_at->toDateTime()),
            updatedAt: \DateTimeImmutable::createFromMutable($model->updated_at->toDateTime()),
        );
    }
}

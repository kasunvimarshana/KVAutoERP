<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\UserProfileRepositoryInterface;
use App\Models\UserProfile;

class UserProfileRepository implements UserProfileRepositoryInterface
{
    public function findByUserId(string $userId): ?UserProfile
    {
        return UserProfile::where('user_id', $userId)->first();
    }

    public function find(string $id): ?UserProfile
    {
        return UserProfile::find($id);
    }

    public function createOrUpdate(string $userId, array $data): UserProfile
    {
        $profile = UserProfile::firstOrNew(['user_id' => $userId]);

        $profile->fill($data);
        $profile->save();

        return $profile->fresh();
    }
}

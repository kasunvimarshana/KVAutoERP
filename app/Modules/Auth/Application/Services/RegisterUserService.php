<?php

namespace Modules\Auth\Application\Services;

use Illuminate\Support\Facades\Hash;
use Modules\Auth\Application\Contracts\RegisterUserServiceInterface;
use Modules\Auth\Domain\Events\UserRegistered;
use Modules\User\Infrastructure\Persistence\Eloquent\Models\UserModel;

class RegisterUserService implements RegisterUserServiceInterface
{
    public function register(array $data): int
    {
        /** @var UserModel $user */
        $user = UserModel::create([
            'tenant_id' => $data['tenant_id'],
            'email' => $data['email'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'active' => true,
        ]);

        UserRegistered::dispatch(
            $user->id,
            $user->email,
            $user->first_name,
            $user->last_name,
        );

        return $user->id;
    }
}

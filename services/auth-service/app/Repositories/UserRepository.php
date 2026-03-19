<?php

namespace App\Repositories;

use App\Models\User;
use Shared\Core\Repositories\BaseRepository;

class UserRepository extends BaseRepository
{
    public function model(): string
    {
        return User::class;
    }

    /**
     * Find user by email
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        return $this->findBy('email', $email);
    }
}

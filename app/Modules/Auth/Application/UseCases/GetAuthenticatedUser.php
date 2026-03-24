<?php

namespace Modules\Auth\Application\UseCases;

use Illuminate\Support\Facades\Auth;
use Modules\User\Infrastructure\Persistence\Eloquent\Models\UserModel;

class GetAuthenticatedUser
{
    /**
     * Return the currently authenticated user model.
     */
    public function execute(): ?UserModel
    {
        /** @var UserModel|null $user */
        $user = Auth::user();

        return $user;
    }
}

<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class UserException extends RuntimeException
{
    public static function notFound(string $userId): self
    {
        return new self("User [{$userId}] not found.", 404);
    }

    public static function emailAlreadyExists(string $email): self
    {
        return new self("A user with email [{$email}] already exists in this tenant.", 422);
    }

    public static function invalidCurrentPassword(): self
    {
        return new self('The current password is incorrect.', 422);
    }

    public static function profileNotFound(string $userId): self
    {
        return new self("Profile for user [{$userId}] not found.", 404);
    }

    public static function tenantMismatch(): self
    {
        return new self('User does not belong to the requesting tenant.', 403);
    }
}

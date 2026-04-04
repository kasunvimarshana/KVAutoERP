<?php

declare(strict_types=1);

namespace Modules\User\Application\DTOs;

use Modules\Core\Application\DTOs\BaseDto;

class CreateUserData extends BaseDto
{
    public int $tenantId;
    public string $name;
    public string $email;
    public string $password;
    public ?string $avatar = null;
    public string $timezone = 'UTC';
    public string $locale = 'en';
    public string $status = 'active';
}

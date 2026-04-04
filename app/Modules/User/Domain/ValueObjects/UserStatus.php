<?php

declare(strict_types=1);

namespace Modules\User\Domain\ValueObjects;

enum UserStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
}

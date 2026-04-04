<?php

declare(strict_types=1);

namespace Modules\User\Application\DTOs;

use Modules\Core\Application\DTOs\BaseDto;

class UpdateProfileData extends BaseDto
{
    public ?string $name = null;
    public ?string $timezone = null;
    public ?string $locale = null;
}

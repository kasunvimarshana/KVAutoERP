<?php

declare(strict_types=1);

namespace Modules\User\Application\DTOs;

use Modules\Core\Application\DTOs\BaseDto;

class ChangePasswordData extends BaseDto
{
    public string $currentPassword;
    public string $newPassword;
    public string $newPasswordConfirmation;
}

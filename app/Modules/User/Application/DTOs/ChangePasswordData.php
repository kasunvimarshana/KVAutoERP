<?php
declare(strict_types=1);
namespace Modules\User\Application\DTOs;
use Modules\Core\Application\DTOs\BaseDto;

class ChangePasswordData extends BaseDto {
    public ?string $current_password = null;
    public ?string $password = null;
    public ?string $password_confirmation = null;
}

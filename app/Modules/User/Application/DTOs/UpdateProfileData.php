<?php
declare(strict_types=1);
namespace Modules\User\Application\DTOs;
use Modules\Core\Application\DTOs\BaseDto;

class UpdateProfileData extends BaseDto {
    public ?string $first_name = null;
    public ?string $last_name = null;
    public ?string $phone = null;
    public ?string $address = null;
}

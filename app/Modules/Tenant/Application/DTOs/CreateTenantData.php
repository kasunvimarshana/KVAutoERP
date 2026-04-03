<?php
declare(strict_types=1);
namespace Modules\Tenant\Application\DTOs;
use Modules\Core\Application\DTOs\BaseDto;

class CreateTenantData extends BaseDto {
    public ?string $name = null;
    public ?array $database_config = null;
    public ?string $domain = null;
    public ?bool $active = null;
    public ?array $metadata = null;
}

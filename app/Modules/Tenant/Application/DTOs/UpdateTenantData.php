<?php
declare(strict_types=1);
namespace Modules\Tenant\Application\DTOs;
use Modules\Core\Application\DTOs\BaseDto;

class UpdateTenantData extends BaseDto {
    public ?int $id = null;
    public ?string $name = null;
    public ?string $domain = null;
    public ?bool $active = null;
    public ?array $metadata = null;
}

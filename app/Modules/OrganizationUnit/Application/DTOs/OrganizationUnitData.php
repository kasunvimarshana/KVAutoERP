<?php
declare(strict_types=1);
namespace Modules\OrganizationUnit\Application\DTOs;
use Modules\Core\Application\DTOs\BaseDto;

class OrganizationUnitData extends BaseDto {
    public ?int $tenant_id = null;
    public ?string $name = null;
    public ?string $code = null;
    public ?string $type = null;
    public ?int $parent_id = null;
    public ?string $description = null;
    public ?array $metadata = null;
}

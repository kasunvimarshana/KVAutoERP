<?php
declare(strict_types=1);
namespace Modules\OrganizationUnit\Application\DTOs;
use Modules\Core\Application\DTOs\BaseDto;

class UpdateOrganizationUnitData extends BaseDto {
    public ?int $id;
    public ?string $name;
    public ?string $code;
    public ?string $description;
    public ?array $metadata;
    public ?int $parent_id;

    protected function initializeNullableProperties(): void
    {
        // intentionally empty: do NOT auto-initialize so we can track provided keys
    }

    public function isProvided(string $key): bool {
        if (!property_exists($this, $key)) {
            return false;
        }
        $prop = new \ReflectionProperty($this, $key);
        return $prop->isInitialized($this);
    }
}

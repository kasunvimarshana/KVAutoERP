<?php
declare(strict_types=1);
namespace Modules\OrganizationUnit\Domain\Events;
use Modules\OrganizationUnit\Domain\Entities\OrganizationUnit;

class OrganizationUnitMoved {
    public function __construct(
        public readonly OrganizationUnit $unit,
        public readonly int $previousParentId
    ) {}
}

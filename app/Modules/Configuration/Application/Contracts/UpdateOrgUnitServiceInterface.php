<?php

declare(strict_types=1);

namespace Modules\Configuration\Application\Contracts;

use Modules\Configuration\Application\DTOs\UpdateOrgUnitData;
use Modules\Configuration\Domain\Entities\OrgUnit;

interface UpdateOrgUnitServiceInterface
{
    public function execute(int $id, UpdateOrgUnitData $data): OrgUnit;
}

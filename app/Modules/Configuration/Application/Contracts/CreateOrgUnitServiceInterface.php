<?php

declare(strict_types=1);

namespace Modules\Configuration\Application\Contracts;

use Modules\Configuration\Application\DTOs\CreateOrgUnitData;
use Modules\Configuration\Domain\Entities\OrgUnit;

interface CreateOrgUnitServiceInterface
{
    public function execute(CreateOrgUnitData $data): OrgUnit;
}

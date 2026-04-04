<?php

declare(strict_types=1);

namespace Modules\Authorization\Application\Contracts;

use Modules\Authorization\Application\DTOs\UpdateRoleData;
use Modules\Authorization\Domain\Entities\Role;

interface UpdateRoleServiceInterface
{
    public function execute(int $id, UpdateRoleData $data): Role;
}

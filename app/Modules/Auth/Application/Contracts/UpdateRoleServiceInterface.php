<?php

declare(strict_types=1);

namespace Modules\Auth\Application\Contracts;

use Modules\Auth\Application\DTOs\UpdateRoleData;
use Modules\Auth\Domain\Entities\Role;

interface UpdateRoleServiceInterface
{
    public function execute(int $id, UpdateRoleData $data): Role;
}

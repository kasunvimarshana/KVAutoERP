<?php

declare(strict_types=1);

namespace Modules\Auth\Application\Contracts;

use Modules\Auth\Application\DTOs\AssignUserRoleData;
use Modules\Auth\Domain\Entities\UserRole;

interface AssignUserRoleServiceInterface
{
    public function execute(AssignUserRoleData $data): UserRole;
}

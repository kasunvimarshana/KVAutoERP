<?php

declare(strict_types=1);

namespace Modules\Authorization\Application\Contracts;

use Modules\Authorization\Application\DTOs\CreateRoleData;
use Modules\Authorization\Domain\Entities\Role;

interface CreateRoleServiceInterface
{
    public function execute(CreateRoleData $data): Role;
}

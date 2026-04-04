<?php

declare(strict_types=1);

namespace Modules\Auth\Application\Contracts;

use Modules\Auth\Application\DTOs\CreateRoleData;
use Modules\Auth\Domain\Entities\Role;

interface CreateRoleServiceInterface
{
    public function execute(CreateRoleData $data): Role;
}

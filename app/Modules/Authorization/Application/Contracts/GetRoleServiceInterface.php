<?php

declare(strict_types=1);

namespace Modules\Authorization\Application\Contracts;

use Modules\Authorization\Domain\Entities\Role;

interface GetRoleServiceInterface
{
    public function execute(int $id): Role;
}

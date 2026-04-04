<?php

declare(strict_types=1);

namespace Modules\Auth\Application\Contracts;

interface DeleteRoleServiceInterface
{
    public function execute(int $id): bool;
}

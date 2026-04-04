<?php

declare(strict_types=1);

namespace Modules\Authorization\Application\Contracts;

interface DeleteRoleServiceInterface
{
    public function execute(int $id): void;
}

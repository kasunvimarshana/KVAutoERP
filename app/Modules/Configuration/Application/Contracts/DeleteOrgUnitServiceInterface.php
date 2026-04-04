<?php

declare(strict_types=1);

namespace Modules\Configuration\Application\Contracts;

interface DeleteOrgUnitServiceInterface
{
    public function execute(int $id): bool;
}

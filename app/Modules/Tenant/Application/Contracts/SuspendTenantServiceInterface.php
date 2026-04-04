<?php

declare(strict_types=1);

namespace Modules\Tenant\Application\Contracts;

use Modules\Tenant\Domain\Entities\Tenant;

interface SuspendTenantServiceInterface
{
    public function execute(int $id): Tenant;
}

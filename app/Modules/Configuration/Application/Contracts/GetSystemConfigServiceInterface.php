<?php

declare(strict_types=1);

namespace Modules\Configuration\Application\Contracts;

use Modules\Configuration\Domain\Entities\SystemConfig;

interface GetSystemConfigServiceInterface
{
    public function execute(string $key, ?int $tenantId = null): ?SystemConfig;
}

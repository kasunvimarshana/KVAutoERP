<?php

declare(strict_types=1);

namespace Modules\Configuration\Application\Contracts;

use Modules\Configuration\Domain\Entities\Setting;

interface GetSettingServiceInterface
{
    public function execute(int $tenantId, string $group, string $key): Setting;
}

<?php

declare(strict_types=1);

namespace Modules\Configuration\Application\Contracts;

use Modules\Configuration\Application\DTOs\UpdateSystemConfigData;
use Modules\Configuration\Domain\Entities\SystemConfig;

interface UpdateSystemConfigServiceInterface
{
    public function execute(UpdateSystemConfigData $data): SystemConfig;
}

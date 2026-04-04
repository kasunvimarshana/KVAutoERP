<?php

declare(strict_types=1);

namespace Modules\Configuration\Application\Contracts;

use Modules\Configuration\Application\DTOs\SetSettingData;
use Modules\Configuration\Domain\Entities\Setting;

interface SetSettingServiceInterface
{
    public function execute(SetSettingData $data): Setting;
}

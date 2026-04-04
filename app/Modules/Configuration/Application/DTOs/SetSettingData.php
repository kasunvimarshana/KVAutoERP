<?php

declare(strict_types=1);

namespace Modules\Configuration\Application\DTOs;

use Modules\Core\Application\DTOs\BaseDto;

class SetSettingData extends BaseDto
{
    public function __construct(
        public int $tenantId,
        public string $group,
        public string $key,
        public mixed $value = null,
        public string $type = 'string',
    ) {
        parent::__construct();
    }
}

<?php

declare(strict_types=1);

namespace Modules\Configuration\Domain\Events;

use Modules\Configuration\Domain\Entities\SystemConfig;
use Modules\Core\Domain\Events\BaseEvent;

class SystemConfigUpdated extends BaseEvent
{
    public function __construct(
        public readonly SystemConfig $config,
    ) {
        parent::__construct($config->tenantId ?? 0);
    }

    public function broadcastWith(): array
    {
        return array_merge(parent::broadcastWith(), [
            'config' => ['id' => $this->config->id, 'key' => $this->config->key],
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Modules\Configuration\Domain\Events;

use Modules\Core\Domain\Events\BaseEvent;

class SettingUpdated extends BaseEvent
{
    public function __construct(
        int $tenantId,
        public readonly string $group,
        public readonly string $key,
        ?int $orgUnitId = null,
    ) {
        parent::__construct($tenantId, $orgUnitId);
    }

    public function broadcastWith(): array
    {
        return array_merge(parent::broadcastWith(), ['group' => $this->group, 'key' => $this->key]);
    }
}

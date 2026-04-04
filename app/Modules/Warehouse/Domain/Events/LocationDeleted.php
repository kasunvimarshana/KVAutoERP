<?php

declare(strict_types=1);

namespace Modules\Warehouse\Domain\Events;

use Modules\Core\Domain\Events\BaseEvent;

class LocationDeleted extends BaseEvent
{
    public function __construct(
        public readonly int $locationId,
        int $tenantId,
    ) {
        parent::__construct($tenantId, $locationId);
    }

    public function broadcastWith(): array
    {
        return array_merge(parent::broadcastWith(), [
            'location' => ['id' => $this->locationId],
        ]);
    }
}

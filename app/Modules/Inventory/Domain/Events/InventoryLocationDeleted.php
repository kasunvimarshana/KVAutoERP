<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Events;

use Modules\Core\Domain\Events\BaseEvent;

class InventoryLocationDeleted extends BaseEvent
{
    public function __construct(public readonly int $locationId, int $tenantId)
    {
        parent::__construct($tenantId, $locationId);
    }

    public function broadcastWith(): array
    {
        return array_merge(parent::broadcastWith(), [
            'id' => $this->locationId,
        ]);
    }
}

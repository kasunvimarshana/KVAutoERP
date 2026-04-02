<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Events;

use Modules\Core\Domain\Events\BaseEvent;

class InventorySerialNumberDeleted extends BaseEvent
{
    public function __construct(public readonly int $serialNumberId, int $tenantId)
    {
        parent::__construct($tenantId, $serialNumberId);
    }

    public function broadcastWith(): array
    {
        return array_merge(parent::broadcastWith(), [
            'id' => $this->serialNumberId,
        ]);
    }
}

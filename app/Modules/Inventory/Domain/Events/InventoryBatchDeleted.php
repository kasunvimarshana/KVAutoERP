<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Events;

use Modules\Core\Domain\Events\BaseEvent;

class InventoryBatchDeleted extends BaseEvent
{
    public function __construct(public readonly int $batchId, int $tenantId)
    {
        parent::__construct($tenantId, $batchId);
    }

    public function broadcastWith(): array
    {
        return array_merge(parent::broadcastWith(), [
            'id' => $this->batchId,
        ]);
    }
}

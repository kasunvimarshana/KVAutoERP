<?php

declare(strict_types=1);

namespace Modules\Warehouse\Domain\Events;

use Modules\Core\Domain\Events\BaseEvent;
use Modules\Warehouse\Domain\Entities\Warehouse;

class WarehouseUpdated extends BaseEvent
{
    public function __construct(
        public readonly Warehouse $warehouse,
    ) {
        parent::__construct($warehouse->tenantId, $warehouse->id);
    }

    public function broadcastWith(): array
    {
        return array_merge(parent::broadcastWith(), [
            'warehouse' => ['id' => $this->warehouse->id, 'name' => $this->warehouse->name],
        ]);
    }
}

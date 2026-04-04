<?php

declare(strict_types=1);

namespace Modules\Warehouse\Domain\Events;

use Modules\Core\Domain\Events\BaseEvent;
use Modules\Warehouse\Domain\Entities\WarehouseLocation;

class LocationMoved extends BaseEvent
{
    public function __construct(
        public readonly WarehouseLocation $location,
        public readonly ?int $previousParentId,
    ) {
        parent::__construct($location->tenantId, $location->id);
    }

    public function broadcastWith(): array
    {
        return array_merge(parent::broadcastWith(), [
            'location'         => ['id' => $this->location->id, 'name' => $this->location->name],
            'previousParentId' => $this->previousParentId,
        ]);
    }
}

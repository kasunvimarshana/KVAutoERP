<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Events;

use Modules\Core\Domain\Events\BaseEvent;

class InventorySettingDeleted extends BaseEvent
{
    public function __construct(public readonly int $settingId, int $tenantId)
    {
        parent::__construct($tenantId, $settingId);
    }

    public function broadcastWith(): array
    {
        return array_merge(parent::broadcastWith(), [
            'id' => $this->settingId,
        ]);
    }
}

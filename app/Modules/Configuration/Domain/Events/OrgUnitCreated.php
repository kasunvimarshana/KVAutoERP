<?php

declare(strict_types=1);

namespace Modules\Configuration\Domain\Events;

use Modules\Configuration\Domain\Entities\OrgUnit;
use Modules\Core\Domain\Events\BaseEvent;

class OrgUnitCreated extends BaseEvent
{
    public function __construct(
        public readonly OrgUnit $orgUnit,
    ) {
        parent::__construct($orgUnit->tenantId, $orgUnit->id);
    }

    public function broadcastWith(): array
    {
        return array_merge(parent::broadcastWith(), [
            'orgUnit' => ['id' => $this->orgUnit->id, 'name' => $this->orgUnit->name],
        ]);
    }
}

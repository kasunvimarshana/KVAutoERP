<?php

declare(strict_types=1);

namespace Modules\Authorization\Domain\Events;

use Modules\Core\Domain\Events\BaseEvent;

class RoleUpdated extends BaseEvent
{
    public int $roleId;

    public function __construct(int $tenantId, int $roleId, ?int $orgUnitId = null)
    {
        parent::__construct($tenantId, $orgUnitId);
        $this->roleId = $roleId;
    }

    public function broadcastWith(): array
    {
        return array_merge(parent::broadcastWith(), ['roleId' => $this->roleId]);
    }
}

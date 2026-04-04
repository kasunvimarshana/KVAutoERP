<?php

declare(strict_types=1);

namespace Modules\Authorization\Domain\Events;

use Modules\Core\Domain\Events\BaseEvent;

class PermissionAssigned extends BaseEvent
{
    public int $roleId;
    public int $permissionId;

    public function __construct(int $tenantId, int $roleId, int $permissionId, ?int $orgUnitId = null)
    {
        parent::__construct($tenantId, $orgUnitId);
        $this->roleId = $roleId;
        $this->permissionId = $permissionId;
    }

    public function broadcastWith(): array
    {
        return array_merge(parent::broadcastWith(), [
            'roleId' => $this->roleId,
            'permissionId' => $this->permissionId,
        ]);
    }
}

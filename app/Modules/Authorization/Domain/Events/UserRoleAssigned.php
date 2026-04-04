<?php

declare(strict_types=1);

namespace Modules\Authorization\Domain\Events;

use Modules\Core\Domain\Events\BaseEvent;

class UserRoleAssigned extends BaseEvent
{
    public int $userId;
    public int $roleId;

    public function __construct(int $tenantId, int $userId, int $roleId, ?int $orgUnitId = null)
    {
        parent::__construct($tenantId, $orgUnitId);
        $this->userId = $userId;
        $this->roleId = $roleId;
    }

    public function broadcastWith(): array
    {
        return array_merge(parent::broadcastWith(), [
            'userId' => $this->userId,
            'roleId' => $this->roleId,
        ]);
    }
}

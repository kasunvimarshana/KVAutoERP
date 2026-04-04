<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\Events;

use Modules\Core\Domain\Events\BaseEvent;

class RoleDeleted extends BaseEvent
{
    public function __construct(
        public readonly int $roleId,
        public readonly int $tenantId,
    ) {
        parent::__construct($tenantId);
    }

    public function broadcastWith(): array
    {
        return array_merge(parent::broadcastWith(), [
            'roleId' => $this->roleId,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\Events;

use Modules\Auth\Domain\Entities\Role;
use Modules\Core\Domain\Events\BaseEvent;

class RoleUpdated extends BaseEvent
{
    public function __construct(
        public readonly Role $role,
    ) {
        parent::__construct($role->tenantId);
    }

    public function broadcastWith(): array
    {
        return array_merge(parent::broadcastWith(), [
            'role' => ['id' => $this->role->id, 'name' => $this->role->name],
        ]);
    }
}

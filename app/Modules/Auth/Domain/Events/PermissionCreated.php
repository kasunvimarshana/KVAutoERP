<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\Events;

use Modules\Auth\Domain\Entities\Permission;
use Modules\Core\Domain\Events\BaseEvent;

class PermissionCreated extends BaseEvent
{
    public function __construct(
        public readonly Permission $permission,
    ) {
        parent::__construct(0);
    }

    public function broadcastWith(): array
    {
        return array_merge(parent::broadcastWith(), [
            'permission' => ['id' => $this->permission->id, 'slug' => $this->permission->slug],
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Modules\User\Domain\Events;

use Modules\Core\Domain\Events\BaseEvent;
use Modules\User\Domain\Entities\User;

class UserUpdated extends BaseEvent
{
    public function __construct(
        public readonly User $user,
    ) {
        parent::__construct($user->tenantId);
    }

    public function broadcastWith(): array
    {
        return array_merge(parent::broadcastWith(), [
            'user' => [
                'id'   => $this->user->id,
                'name' => $this->user->name,
            ],
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Modules\User\Domain\Events;

use Modules\Core\Domain\Events\BaseEvent;

class UserAvatarUpdated extends BaseEvent
{
    public function __construct(
        public readonly int $userId,
        public readonly int $tenantId,
        public readonly ?string $avatar,
    ) {
        parent::__construct($tenantId);
    }

    public function broadcastWith(): array
    {
        return array_merge(parent::broadcastWith(), [
            'userId' => $this->userId,
            'avatar' => $this->avatar,
        ]);
    }
}

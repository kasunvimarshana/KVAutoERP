<?php

declare(strict_types=1);

namespace Modules\User\Domain\Events;

use Modules\Core\Domain\Events\BaseEvent;

class UserProfileUpdated extends BaseEvent
{
    public int $userId;

    public function __construct(int $tenantId, int $userId, ?int $orgUnitId = null)
    {
        parent::__construct($tenantId, $orgUnitId);
        $this->userId = $userId;
    }

    public function broadcastWith(): array
    {
        return array_merge(parent::broadcastWith(), ['userId' => $this->userId]);
    }
}

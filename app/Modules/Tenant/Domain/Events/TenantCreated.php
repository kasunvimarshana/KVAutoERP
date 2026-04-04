<?php

declare(strict_types=1);

namespace Modules\Tenant\Domain\Events;

use Modules\Core\Domain\Events\BaseEvent;
use Modules\Tenant\Domain\Entities\Tenant;

class TenantCreated extends BaseEvent
{
    public function __construct(
        public readonly Tenant $tenant,
    ) {
        parent::__construct($tenant->id);
    }

    public function broadcastWith(): array
    {
        return array_merge(parent::broadcastWith(), [
            'tenant' => [
                'id'   => $this->tenant->id,
                'name' => $this->tenant->name,
                'slug' => $this->tenant->slug,
            ],
        ]);
    }
}

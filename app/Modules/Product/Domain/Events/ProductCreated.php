<?php

declare(strict_types=1);

namespace Modules\Product\Domain\Events;

use Modules\Core\Domain\Events\BaseEvent;

class ProductCreated extends BaseEvent
{
    public function __construct(
        int $tenantId,
        public readonly int $productId,
        ?int $orgUnitId = null,
    ) {
        parent::__construct($tenantId, $orgUnitId);
    }

    public function broadcastWith(): array
    {
        return array_merge(parent::broadcastWith(), ['productId' => $this->productId]);
    }
}

<?php

declare(strict_types=1);

namespace Modules\Product\Domain\Events;

use Modules\Core\Domain\Events\BaseEvent;

class ProductDeleted extends BaseEvent
{
    public function __construct(
        public readonly int $productId,
        int $tenantId,
    ) {
        parent::__construct($tenantId, $productId);
    }

    public function broadcastWith(): array
    {
        return array_merge(parent::broadcastWith(), [
            'product' => ['id' => $this->productId],
        ]);
    }
}

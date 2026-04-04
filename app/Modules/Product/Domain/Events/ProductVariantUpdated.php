<?php

declare(strict_types=1);

namespace Modules\Product\Domain\Events;

use Modules\Core\Domain\Events\BaseEvent;
use Modules\Product\Domain\Entities\ProductVariant;

class ProductVariantUpdated extends BaseEvent
{
    public function __construct(
        public readonly ProductVariant $variant,
    ) {
        parent::__construct($variant->tenantId, $variant->id);
    }

    public function broadcastWith(): array
    {
        return array_merge(parent::broadcastWith(), [
            'variant' => ['id' => $this->variant->id, 'sku' => $this->variant->sku],
        ]);
    }
}

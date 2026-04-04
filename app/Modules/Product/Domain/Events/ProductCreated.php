<?php

declare(strict_types=1);

namespace Modules\Product\Domain\Events;

use Modules\Core\Domain\Events\BaseEvent;
use Modules\Product\Domain\Entities\Product;

class ProductCreated extends BaseEvent
{
    public function __construct(
        public readonly Product $product,
    ) {
        parent::__construct($product->tenantId, $product->id);
    }

    public function broadcastWith(): array
    {
        return array_merge(parent::broadcastWith(), [
            'product' => ['id' => $this->product->id, 'sku' => $this->product->sku],
        ]);
    }
}

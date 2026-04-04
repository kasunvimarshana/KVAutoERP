<?php

declare(strict_types=1);

namespace Modules\Product\Domain\Events;

use Modules\Core\Domain\Events\BaseEvent;
use Modules\Product\Domain\Entities\ProductCategory;

class ProductCategoryCreated extends BaseEvent
{
    public function __construct(
        public readonly ProductCategory $category,
    ) {
        parent::__construct($category->tenantId, $category->id);
    }

    public function broadcastWith(): array
    {
        return array_merge(parent::broadcastWith(), [
            'category' => ['id' => $this->category->id, 'name' => $this->category->name],
        ]);
    }
}

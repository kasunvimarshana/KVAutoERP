<?php

namespace App\Modules\Product\Listeners;

use App\Modules\Product\Events\ProductDeleted;
use App\Services\RabbitMQService;
use Illuminate\Contracts\Queue\ShouldQueue;

class PublishProductDeleted implements ShouldQueue
{
    public function __construct(private RabbitMQService $rabbitMQ) {}

    public function handle(ProductDeleted $event): void
    {
        $this->rabbitMQ->publish('product.deleted', [
            'id' => $event->product->id,
            'name' => $event->product->name,
            'sku' => $event->product->sku,
        ]);
    }
}

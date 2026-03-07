<?php

namespace App\Modules\Product\Listeners;

use App\Modules\Product\Events\ProductCreated;
use App\Services\RabbitMQService;
use Illuminate\Contracts\Queue\ShouldQueue;

class PublishProductCreated implements ShouldQueue
{
    public function __construct(private RabbitMQService $rabbitMQ) {}

    public function handle(ProductCreated $event): void
    {
        $this->rabbitMQ->publish('product.created', [
            'id' => $event->product->id,
            'name' => $event->product->name,
            'sku' => $event->product->sku,
            'price' => $event->product->price,
            'stock_quantity' => $event->product->stock_quantity,
        ]);
    }
}

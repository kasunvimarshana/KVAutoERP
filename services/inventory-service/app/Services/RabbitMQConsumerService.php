<?php

namespace App\Services;

use App\Modules\Inventory\Listeners\HandleProductCreatedEvent;
use App\Modules\Inventory\Listeners\HandleProductDeletedEvent;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * RabbitMQConsumerService listens for events published by other microservices.
 */
class RabbitMQConsumerService
{
    public function __construct(
        private readonly HandleProductCreatedEvent $productCreatedHandler,
        private readonly HandleProductDeletedEvent $productDeletedHandler
    ) {}

    public function startConsuming(): void
    {
        $connection = new AMQPStreamConnection(
            config('rabbitmq.host', 'rabbitmq'),
            config('rabbitmq.port', 5672),
            config('rabbitmq.username', 'guest'),
            config('rabbitmq.password', 'guest'),
            config('rabbitmq.vhost', '/')
        );

        $channel = $connection->channel();

        $exchange = 'inventory_exchange';
        $queue    = 'inventory_service_queue';

        $channel->exchange_declare($exchange, 'topic', false, true, false);
        $channel->queue_declare($queue, false, true, false, false);
        $channel->queue_bind($queue, $exchange, 'product.#');

        Log::info('Inventory service consumer started, waiting for events...');

        $channel->basic_consume(
            $queue, '', false, false, false, false,
            function (AMQPMessage $msg) use ($channel) {
                try {
                    $data      = json_decode($msg->body, true);
                    $eventType = $data['event'] ?? '';

                    Log::info('Received event', ['event' => $eventType]);

                    match ($eventType) {
                        'ProductCreated' => $this->productCreatedHandler->handle($data),
                        'ProductDeleted' => $this->productDeletedHandler->handle($data),
                        default          => Log::warning('Unknown event type', ['event' => $eventType]),
                    };

                    $channel->basic_ack($msg->delivery_info['delivery_tag']);
                } catch (\Exception $e) {
                    Log::error('Error processing event', ['error' => $e->getMessage()]);
                    $channel->basic_nack($msg->delivery_info['delivery_tag'], false, true);
                }
            }
        );

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }
}

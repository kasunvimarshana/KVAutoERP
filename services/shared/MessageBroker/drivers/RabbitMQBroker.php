<?php

namespace App\Shared\MessageBroker\Drivers;

use App\Shared\MessageBroker\Contracts\MessageBrokerInterface;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

class RabbitMQBroker implements MessageBrokerInterface
{
    private ?AMQPStreamConnection $connection = null;
    private array $channels = [];
    private string $tenantId;
    private array $config;
    private int $maxRetries = 3;

    public function __construct(array $config, string $tenantId = 'default')
    {
        $this->config = $config;
        $this->tenantId = $tenantId;
        $this->connect();
    }

    private function connect(): void
    {
        $this->connection = new AMQPStreamConnection(
            $this->config['host'],
            $this->config['port'],
            $this->config['user'],
            $this->config['password'],
            $this->config['vhost'] ?? '/'
        );
    }

    private function getChannel(string $id = 'default')
    {
        if (!isset($this->channels[$id]) || !$this->channels[$id]->is_open()) {
            $this->channels[$id] = $this->connection->channel();
        }
        return $this->channels[$id];
    }

    private function prefixedTopic(string $topic): string
    {
        return "{$this->tenantId}.{$topic}";
    }

    private function declareQueueAndExchange(string $topic, $channel): void
    {
        $exchange = $this->prefixedTopic($topic);
        $queue    = $this->prefixedTopic($topic);
        $dlxExchange = $exchange . '.dlx';
        $dlxQueue    = $queue . '.dead';

        $channel->exchange_declare($dlxExchange, 'direct', false, true, false);
        $channel->queue_declare($dlxQueue, false, true, false, false);
        $channel->queue_bind($dlxQueue, $dlxExchange, $queue);

        $args = new AMQPTable([
            'x-dead-letter-exchange'    => $dlxExchange,
            'x-dead-letter-routing-key' => $queue,
        ]);
        $channel->exchange_declare($exchange, 'direct', false, true, false);
        $channel->queue_declare($queue, false, true, false, false, false, $args);
        $channel->queue_bind($queue, $exchange, $queue);
    }

    public function publish(string $topic, array $message, array $options = []): bool
    {
        $attempt = 0;
        $delay = 500000; // 0.5s
        while ($attempt < $this->maxRetries) {
            try {
                if (!$this->isConnected()) {
                    $this->connect();
                }
                $channel = $this->getChannel();
                $this->declareQueueAndExchange($topic, $channel);
                $body = json_encode(array_merge($message, [
                    '_tenant_id' => $this->tenantId,
                    '_message_id' => uniqid('msg_', true),
                    '_timestamp' => time(),
                ]));
                $msg = new AMQPMessage($body, [
                    'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                    'content_type'  => 'application/json',
                    'message_id'    => uniqid('', true),
                ]);
                $channel->basic_publish($msg, $this->prefixedTopic($topic), $this->prefixedTopic($topic));
                return true;
            } catch (\Exception $e) {
                $attempt++;
                if ($attempt >= $this->maxRetries) {
                    throw $e;
                }
                usleep($delay);
                $delay *= 2;
                $this->connect();
            }
        }
        return false;
    }

    public function subscribe(string $topic, callable $handler, array $options = []): void
    {
        $channel = $this->getChannel($topic);
        $this->declareQueueAndExchange($topic, $channel);
        $queue = $this->prefixedTopic($topic);

        $channel->basic_qos(null, 1, null);
        $channel->basic_consume(
            $queue,
            $options['consumer_tag'] ?? '',
            false, false, false, false,
            function ($msg) use ($handler) {
                try {
                    $data = json_decode($msg->body, true);
                    $handler($data, $msg->delivery_info['delivery_tag']);
                } catch (\Exception $e) {
                    $msg->nack(false, true);
                    return;
                }
            }
        );

        while ($channel->is_consuming()) {
            $channel->wait();
        }
    }

    public function publishBatch(string $topic, array $messages): bool
    {
        if (!$this->isConnected()) {
            $this->connect();
        }
        $channel = $this->getChannel();
        $channel->confirm_select();
        $this->declareQueueAndExchange($topic, $channel);

        foreach ($messages as $message) {
            $body = json_encode(array_merge($message, [
                '_tenant_id'  => $this->tenantId,
                '_message_id' => uniqid('msg_', true),
                '_timestamp'  => time(),
            ]));
            $msg = new AMQPMessage($body, [
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                'content_type'  => 'application/json',
            ]);
            $channel->batch_basic_publish($msg, $this->prefixedTopic($topic), $this->prefixedTopic($topic));
        }
        $channel->publish_batch();
        $channel->wait_for_pending_acks(5.0);
        return true;
    }

    public function ack(string $messageId): void
    {
        foreach ($this->channels as $channel) {
            if ($channel->is_open()) {
                $channel->basic_ack($messageId);
                return;
            }
        }
    }

    public function nack(string $messageId, bool $requeue = true): void
    {
        foreach ($this->channels as $channel) {
            if ($channel->is_open()) {
                $channel->basic_nack($messageId, false, $requeue);
                return;
            }
        }
    }

    public function isConnected(): bool
    {
        return $this->connection !== null && $this->connection->isConnected();
    }

    public function disconnect(): void
    {
        foreach ($this->channels as $channel) {
            if ($channel->is_open()) {
                $channel->close();
            }
        }
        if ($this->isConnected()) {
            $this->connection->close();
        }
        $this->channels = [];
        $this->connection = null;
    }
}

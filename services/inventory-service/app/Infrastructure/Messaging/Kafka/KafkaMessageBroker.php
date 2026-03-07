<?php

namespace App\Infrastructure\Messaging\Kafka;

use App\Infrastructure\Messaging\Contracts\MessageBrokerInterface;
use Illuminate\Support\Facades\Log;

/**
 * Kafka message broker implementation.
 *
 * Uses the ext-rdkafka PHP extension when available.
 * Falls back to a no-op stub so the application boots without the extension
 * (useful in development or when RabbitMQ is the active broker).
 */
class KafkaMessageBroker implements MessageBrokerInterface
{
    private bool $rdkafkaAvailable;
    private ?object $producer = null;
    private ?object $consumer = null;
    private string $brokers;
    private bool $connected = false;

    public function __construct(array $config = [])
    {
        $this->brokers         = $config['brokers'] ?? config('messaging.kafka.brokers', 'kafka:9092');
        $this->rdkafkaAvailable = extension_loaded('rdkafka');

        if (! $this->rdkafkaAvailable) {
            Log::warning('[Kafka] ext-rdkafka is not loaded. KafkaMessageBroker is running in stub mode.');
        }
    }

    // ─── Connection helpers ───────────────────────────────────────────────────

    private function connectProducer(): void
    {
        if (! $this->rdkafkaAvailable) {
            return;
        }

        $conf = new \RdKafka\Conf();
        $conf->set('metadata.broker.list', $this->brokers);
        $conf->set('socket.timeout.ms', '10000');
        $conf->set('enable.idempotence', 'true');
        $conf->set('message.send.max.retries', '5');
        $conf->setDrMsgCb(function (\RdKafka\Producer $kafka, \RdKafka\Message $message): void {
            if ($message->err !== RD_KAFKA_RESP_ERR_NO_ERROR) {
                Log::error('[Kafka] Delivery failure: ' . $message->errstr());
            }
        });

        $this->producer  = new \RdKafka\Producer($conf);
        $this->connected = true;
    }

    private function connectConsumer(string $groupId): void
    {
        if (! $this->rdkafkaAvailable) {
            return;
        }

        $conf = new \RdKafka\Conf();
        $conf->set('metadata.broker.list', $this->brokers);
        $conf->set('group.id', $groupId);
        $conf->set('auto.offset.reset', 'earliest');
        $conf->set('enable.auto.commit', 'false');

        $this->consumer  = new \RdKafka\KafkaConsumer($conf);
        $this->connected = true;
    }

    // ─── Interface implementation ─────────────────────────────────────────────

    public function publish(string $topic, array $message, array $options = []): void
    {
        if (! $this->rdkafkaAvailable) {
            Log::warning("[Kafka][stub] publish to topic={$topic}", $message);
            return;
        }

        if ($this->producer === null) {
            $this->connectProducer();
        }

        try {
            $kafkaTopic = $this->producer->newTopic($topic);
            $payload    = json_encode($message, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
            $partition  = $options['partition'] ?? RD_KAFKA_PARTITION_UA;
            $key        = $options['key']       ?? null;

            $kafkaTopic->produce($partition, 0, $payload, $key);
            $this->producer->poll(0); // Non-blocking flush of internal queue

            // Ensure delivery within timeout
            $timeout = (int) ($options['flush_timeout_ms'] ?? 10000);
            $result  = $this->producer->flush($timeout);

            if ($result !== RD_KAFKA_RESP_ERR_NO_ERROR) {
                Log::error("[Kafka] Flush timed out for topic={$topic}");
            }
        } catch (\Throwable $e) {
            Log::error("[Kafka] Publish failed: {$e->getMessage()}");
            throw new \RuntimeException("Kafka publish failed: {$e->getMessage()}", 0, $e);
        }
    }

    public function subscribe(string $topic, callable $handler, array $options = []): void
    {
        if (! $this->rdkafkaAvailable) {
            Log::warning("[Kafka][stub] subscribe to topic={$topic} – no-op");
            return;
        }

        $groupId = $options['group_id'] ?? config('app.name', 'inventory-service');

        if ($this->consumer === null) {
            $this->connectConsumer($groupId);
        }

        $this->consumer->subscribe([$topic]);
        Log::info("[Kafka] Subscribed to topic={$topic}");

        $timeout = (int) ($options['timeout_ms'] ?? 120000);

        while (true) {
            $message = $this->consumer->consume($timeout);

            if ($message === null) {
                continue;
            }

            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    try {
                        $payload = json_decode($message->payload, true, 512, JSON_THROW_ON_ERROR);
                        $handler($payload, $message);
                        $this->acknowledge($message);
                    } catch (\Throwable $e) {
                        Log::error("[Kafka] Handler exception: {$e->getMessage()}");
                        $this->reject($message);
                    }
                    break;

                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                    Log::debug("[Kafka] End of partition reached for topic={$topic}");
                    break;

                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    break;

                default:
                    Log::error("[Kafka] Consumer error: {$message->errstr()}");
                    break;
            }
        }
    }

    public function acknowledge(mixed $message): void
    {
        if (! $this->rdkafkaAvailable || $this->consumer === null) {
            return;
        }

        if ($message instanceof \RdKafka\Message) {
            $this->consumer->commit($message);
        }
    }

    public function reject(mixed $message, bool $requeue = false): void
    {
        // Kafka has no built-in rejection; log and optionally seek back for requeue
        if (! $this->rdkafkaAvailable || $this->consumer === null) {
            return;
        }

        if ($message instanceof \RdKafka\Message) {
            Log::warning("[Kafka] Message rejected (requeue={$requeue}) – offset={$message->offset}");

            if ($requeue) {
                // Seek back to the same offset so the message is re-consumed
                $this->consumer->assign([
                    new \RdKafka\TopicPartition($message->topic_name, $message->partition, $message->offset),
                ]);
            }
        }
    }

    public function disconnect(): void
    {
        $this->producer  = null;
        $this->consumer  = null;
        $this->connected = false;
    }

    public function isConnected(): bool
    {
        return $this->connected;
    }

    public function __destruct()
    {
        $this->disconnect();
    }
}

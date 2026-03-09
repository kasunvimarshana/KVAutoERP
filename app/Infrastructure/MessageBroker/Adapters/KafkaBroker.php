<?php

declare(strict_types=1);

namespace App\Infrastructure\MessageBroker\Adapters;

use App\Core\Contracts\MessageBroker\MessageBrokerInterface;
use Illuminate\Support\Facades\Log;

/**
 * KafkaBroker
 *
 * Adapter for Apache Kafka via the rdkafka PHP extension.
 * Configuration via environment variables:
 *   KAFKA_BROKERS, KAFKA_GROUP_ID, KAFKA_SECURITY_PROTOCOL, etc.
 *
 * To activate: MESSAGE_BROKER_DRIVER=kafka
 */
class KafkaBroker implements MessageBrokerInterface
{
    /** @var \RdKafka\Producer|null */
    private mixed $producer = null;

    /** @var \RdKafka\KafkaConsumer|null */
    private mixed $consumer = null;

    public function __construct(
        private readonly string $brokers,
        private readonly string $groupId = 'saas-inventory'
    ) {}

    public function publish(string $topic, array $payload, array $options = []): bool
    {
        try {
            $producer = $this->getProducer();
            $kafkaTopic = $producer->newTopic($topic);
            $kafkaTopic->produce(
                RD_KAFKA_PARTITION_UA,
                0,
                json_encode($payload),
                $options['key'] ?? null
            );
            $producer->flush(10000);

            Log::debug("[MessageBroker:Kafka] Published to [{$topic}]");
            return true;
        } catch (\Throwable $e) {
            Log::error("[MessageBroker:Kafka] Publish failed: {$e->getMessage()}");
            return false;
        }
    }

    public function subscribe(string $topic, callable $callback, array $options = []): void
    {
        try {
            $consumer = $this->getConsumer();
            $consumer->subscribe([$topic]);

            while (true) {
                $message = $consumer->consume(120 * 1000);

                if ($message->err === RD_KAFKA_RESP_ERR_NO_ERROR) {
                    $callback(json_decode($message->payload, true) ?? []);
                } elseif ($message->err !== RD_KAFKA_RESP_ERR__TIMED_OUT) {
                    Log::warning("[MessageBroker:Kafka] Consumer error: {$message->errstr()}");
                }
            }
        } catch (\Throwable $e) {
            Log::error("[MessageBroker:Kafka] Subscribe failed: {$e->getMessage()}");
        }
    }

    public function acknowledge(mixed $messageId): void
    {
        // Kafka uses offset commits; handled automatically in the consumer loop
    }

    public function reject(mixed $messageId, bool $requeue = false): void
    {
        // Kafka does not support NACK natively; use dead-letter topics instead
    }

    public function isHealthy(): bool
    {
        try {
            $producer = $this->getProducer();
            $metadata = $producer->getMetadata(true, null, 5000);
            return $metadata !== null;
        } catch (\Throwable) {
            return false;
        }
    }

    // -------------------------------------------------------------------------
    //  Private helpers
    // -------------------------------------------------------------------------

    /** @return \RdKafka\Producer */
    private function getProducer(): mixed
    {
        if ($this->producer === null) {
            $conf = new \RdKafka\Conf();
            $conf->set('metadata.broker.list', $this->brokers);
            $this->producer = new \RdKafka\Producer($conf);
        }

        return $this->producer;
    }

    /** @return \RdKafka\KafkaConsumer */
    private function getConsumer(): mixed
    {
        if ($this->consumer === null) {
            $conf = new \RdKafka\Conf();
            $conf->set('metadata.broker.list', $this->brokers);
            $conf->set('group.id', $this->groupId);
            $conf->set('auto.offset.reset', 'earliest');
            $this->consumer = new \RdKafka\KafkaConsumer($conf);
        }

        return $this->consumer;
    }
}

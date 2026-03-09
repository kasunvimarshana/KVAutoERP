<?php

declare(strict_types=1);

namespace App\Infrastructure\Messaging;

use Illuminate\Support\Facades\Log;

/**
 * Kafka Message Broker Implementation.
 *
 * Implements the pluggable MessageBrokerInterface for Apache Kafka.
 * Requires the rdkafka PHP extension.
 */
class KafkaBroker implements MessageBrokerInterface
{
    private ?\RdKafka\Producer $producer = null;

    public function __construct(
        private readonly string $brokers,
        private readonly string $groupId,
    ) {}

    /**
     * Publish a message to a Kafka topic.
     *
     * @param  string               $topic
     * @param  array<string, mixed> $message
     * @param  array<string, mixed> $options partition, key
     * @return bool
     */
    public function publish(string $topic, array $message, array $options = []): bool
    {
        try {
            $producer = $this->getProducer();
            $kafkaTopic = $producer->newTopic($topic);

            $body = json_encode(array_merge($message, [
                '__topic'     => $topic,
                '__timestamp' => now()->toISOString(),
                '__id'        => (string) \Illuminate\Support\Str::uuid(),
            ]), JSON_THROW_ON_ERROR);

            $partition = $options['partition'] ?? RD_KAFKA_PARTITION_UA;
            $key       = $options['key'] ?? null;

            $kafkaTopic->produce($partition, 0, $body, $key);
            $producer->poll(0);

            // Flush to ensure delivery
            $producer->flush(10000);

            return true;
        } catch (\Throwable $e) {
            Log::error('Kafka publish failed', [
                'topic' => $topic,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Subscribe to a Kafka topic.
     *
     * @param  string              $topic
     * @param  callable            $handler function(array $message, \RdKafka\Message $raw): void
     * @param  array<string, mixed> $options
     * @return void
     */
    public function subscribe(string $topic, callable $handler, array $options = []): void
    {
        $conf = new \RdKafka\Conf();
        $conf->set('group.id', $this->groupId);
        $conf->set('metadata.broker.list', $this->brokers);
        $conf->set('auto.offset.reset', $options['auto_offset_reset'] ?? 'earliest');
        $conf->set('enable.auto.commit', 'false');

        $consumer = new \RdKafka\KafkaConsumer($conf);
        $consumer->subscribe([$topic]);

        while (true) {
            $message = $consumer->consume(120 * 1000);

            if ($message === null) {
                continue;
            }

            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    try {
                        $data = json_decode($message->payload, true, 512, JSON_THROW_ON_ERROR);
                        $handler($data, $message);
                        $consumer->commit($message);
                    } catch (\Throwable $e) {
                        Log::error("Kafka message processing failed [{$topic}]", ['error' => $e->getMessage()]);
                    }
                    break;

                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    break;

                default:
                    Log::error("Kafka consumer error [{$topic}]", ['error' => $message->errstr()]);
            }
        }
    }

    public function acknowledge(mixed $message): void
    {
        // Kafka uses commit() - handled in subscribe() loop
    }

    public function reject(mixed $message, bool $requeue = false): void
    {
        // Kafka doesn't have nack - messages are re-consumed if not committed
    }

    public function isConnected(): bool
    {
        return $this->producer !== null;
    }

    public function getDriver(): string
    {
        return 'kafka';
    }

    /**
     * Get or create Kafka producer.
     *
     * @return \RdKafka\Producer
     */
    private function getProducer(): \RdKafka\Producer
    {
        if ($this->producer === null) {
            $conf = new \RdKafka\Conf();
            $conf->set('metadata.broker.list', $this->brokers);
            $this->producer = new \RdKafka\Producer($conf);
        }

        return $this->producer;
    }
}

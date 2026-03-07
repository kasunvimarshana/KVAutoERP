<?php

namespace App\Shared\MessageBroker\Drivers;

use App\Shared\MessageBroker\Contracts\MessageBrokerInterface;

class KafkaBroker implements MessageBrokerInterface
{
    private ?\RdKafka\Producer $producer = null;
    private ?\RdKafka\KafkaConsumer $consumer = null;
    private array $config;
    private string $tenantId;
    private bool $connected = false;

    public function __construct(array $config, string $tenantId = 'default')
    {
        $this->config   = $config;
        $this->tenantId = $tenantId;
        $this->connect();
    }

    private function connect(): void
    {
        $conf = new \RdKafka\Conf();
        $conf->set('bootstrap.servers', $this->config['brokers']);
        if (!empty($this->config['sasl_username'])) {
            $conf->set('security.protocol', 'SASL_SSL');
            $conf->set('sasl.mechanisms', 'PLAIN');
            $conf->set('sasl.username', $this->config['sasl_username']);
            $conf->set('sasl.password', $this->config['sasl_password']);
        }
        $this->producer  = new \RdKafka\Producer($conf);
        $this->connected = true;
    }

    private function topicName(string $topic): string
    {
        return "{$this->tenantId}.{$topic}";
    }

    private function partitionKey(): int
    {
        return abs(crc32($this->tenantId)) % ($this->config['num_partitions'] ?? 10);
    }

    public function publish(string $topic, array $message, array $options = []): bool
    {
        $topicName = $this->topicName($topic);
        $kafkaTopic = $this->producer->newTopic($topicName);
        $payload = json_encode(array_merge($message, [
            '_tenant_id'  => $this->tenantId,
            '_message_id' => uniqid('msg_', true),
            '_timestamp'  => time(),
        ]));
        $kafkaTopic->produce($this->partitionKey(), 0, $payload, $this->tenantId);
        $this->producer->poll(0);
        $result = $this->producer->flush(10000);
        return $result === RD_KAFKA_RESP_ERR_NO_ERROR;
    }

    public function subscribe(string $topic, callable $handler, array $options = []): void
    {
        $conf = new \RdKafka\Conf();
        $conf->set('bootstrap.servers', $this->config['brokers']);
        $conf->set('group.id', $options['group_id'] ?? "tenant_{$this->tenantId}_{$topic}");
        $conf->set('auto.offset.reset', $options['auto_offset_reset'] ?? 'earliest');
        $conf->set('enable.auto.commit', 'false');

        $this->consumer = new \RdKafka\KafkaConsumer($conf);
        $this->consumer->subscribe([$this->topicName($topic)]);

        while (true) {
            $msg = $this->consumer->consume(120 * 1000);
            if ($msg === null) continue;
            switch ($msg->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    $data = json_decode($msg->payload, true);
                    $handler($data, $msg->offset);
                    $this->consumer->commit($msg);
                    break;
                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    break;
                default:
                    throw new \RuntimeException($msg->errstr(), $msg->err);
            }
        }
    }

    public function publishBatch(string $topic, array $messages): bool
    {
        $topicName  = $this->topicName($topic);
        $kafkaTopic = $this->producer->newTopic($topicName);
        foreach ($messages as $message) {
            $payload = json_encode(array_merge($message, [
                '_tenant_id'  => $this->tenantId,
                '_message_id' => uniqid('msg_', true),
                '_timestamp'  => time(),
            ]));
            $kafkaTopic->produce($this->partitionKey(), 0, $payload, $this->tenantId);
            $this->producer->poll(0);
        }
        $result = $this->producer->flush(30000);
        return $result === RD_KAFKA_RESP_ERR_NO_ERROR;
    }

    public function ack(string $messageId): void
    {
        // Kafka uses offset-based commits; no-op for individual ack
    }

    public function nack(string $messageId, bool $requeue = true): void
    {
        // Kafka: seek back or simply don't commit; no-op here
    }

    public function isConnected(): bool
    {
        return $this->connected && $this->producer !== null;
    }

    public function disconnect(): void
    {
        if ($this->producer) {
            $this->producer->flush(5000);
            $this->producer = null;
        }
        if ($this->consumer) {
            $this->consumer->close();
            $this->consumer = null;
        }
        $this->connected = false;
    }
}

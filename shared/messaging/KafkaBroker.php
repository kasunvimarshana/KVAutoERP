<?php

declare(strict_types=1);

namespace App\Shared\Messaging;

use App\Shared\Contracts\MessageBrokerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RdKafka\Conf;
use RdKafka\KafkaConsumer;
use RdKafka\Message;
use RdKafka\Producer;
use RdKafka\ProducerTopic;
use RdKafka\TopicConf;

/**
 * Kafka Message Broker.
 *
 * Full implementation of {@see MessageBrokerInterface} using the ext-rdkafka
 * PHP extension (librdkafka bindings).
 *
 * Features:
 *  - Async & sync (flush) producer modes
 *  - Batch publish via queued delivery reports
 *  - Consumer group management
 *  - Manual offset commit on acknowledge
 *  - Health check via metadata fetch
 */
final class KafkaBroker implements MessageBrokerInterface
{
    private ?Producer $producer = null;

    /** ProducerTopic cache keyed by topic name. */
    private array $producerTopics = [];

    /** Consumer instances keyed by topic/group combo. */
    private array $consumers = [];

    private const int FLUSH_TIMEOUT_MS  = 10_000;
    private const int POLL_TIMEOUT_MS   = 1_000;
    private const int METADATA_TIMEOUT  = 5_000;

    /**
     * @param  string          $brokers       Comma-separated broker list (e.g. "kafka:9092").
     * @param  string          $groupId       Consumer group ID.
     * @param  string          $securityProto PLAINTEXT|SSL|SASL_PLAINTEXT|SASL_SSL
     * @param  string          $saslMechanism PLAIN|SCRAM-SHA-256|SCRAM-SHA-512
     * @param  string          $saslUsername
     * @param  string          $saslPassword
     * @param  string          $autoOffsetReset earliest|latest|none
     * @param  int             $sessionTimeoutMs
     * @param  LoggerInterface $logger
     */
    public function __construct(
        private readonly string $brokers,
        private readonly string $groupId = 'kv-saas-consumers',
        private readonly string $securityProto = 'PLAINTEXT',
        private readonly string $saslMechanism = 'PLAIN',
        private readonly string $saslUsername = '',
        private readonly string $saslPassword = '',
        private readonly string $autoOffsetReset = 'earliest',
        private readonly int $sessionTimeoutMs = 45_000,
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    // MessageBrokerInterface
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * {@inheritDoc}
     *
     * Options:
     *  - partition (int)     RD_KAFKA_PARTITION_UA = auto-assign.
     *  - key       (string)  Kafka message key for partitioning.
     *  - headers   (array)   Key/value headers added to the message.
     *  - flush     (bool)    Whether to flush after producing (default: false for throughput).
     */
    public function publish(string $topic, array $message, array $options = []): bool
    {
        $producer      = $this->getProducer();
        $producerTopic = $this->getProducerTopic($producer, $topic);

        $partition = $options['partition'] ?? RD_KAFKA_PARTITION_UA;
        $key       = $options['key'] ?? null;
        $payload   = json_encode($message, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

        $headers = $options['headers'] ?? [];
        $headers['message_id'] ??= (string) \Illuminate\Support\Str::uuid();
        $headers['timestamp']  ??= (string) time();

        $producerTopic->producev(
            partition: $partition,
            msgflags: 0,
            payload: $payload,
            key: $key,
            headers: $headers,
        );

        $producer->poll(0);

        if ($options['flush'] ?? false) {
            $result = $producer->flush(self::FLUSH_TIMEOUT_MS);
            if ($result !== RD_KAFKA_RESP_ERR_NO_ERROR) {
                throw new \RuntimeException(
                    '[Kafka] Flush failed with error: ' . rd_kafka_err2str($result)
                );
            }
        }

        $this->logger->debug('[Kafka] Message produced', [
            'topic'     => $topic,
            'partition' => $partition,
            'key'       => $key,
        ]);

        return true;
    }

    /**
     * {@inheritDoc}
     *
     * Blocks until $options['max_messages'] have been consumed or the process
     * is interrupted.
     *
     * Options:
     *  - timeout_ms   (int)  Consumer poll timeout in ms (default: 1000).
     *  - max_messages (int)  Stop after N messages (default: 0 = infinite).
     */
    public function subscribe(string $topic, callable $handler, array $options = []): void
    {
        $consumer    = $this->buildConsumer();
        $timeoutMs   = (int) ($options['timeout_ms'] ?? self::POLL_TIMEOUT_MS);
        $maxMessages = (int) ($options['max_messages'] ?? 0);
        $msgCount    = 0;

        $consumer->subscribe([$topic]);

        $this->logger->info('[Kafka] Subscribed', [
            'topic'    => $topic,
            'group_id' => $this->groupId,
        ]);

        while (true) {
            $message = $consumer->consume($timeoutMs);

            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    try {
                        $decoded = json_decode(
                            $message->payload,
                            associative: true,
                            flags: JSON_THROW_ON_ERROR,
                        );
                        $handler($message, $decoded);
                    } catch (\Throwable $e) {
                        $this->logger->error('[Kafka] Handler exception', [
                            'topic' => $topic,
                            'error' => $e->getMessage(),
                        ]);
                    }

                    $msgCount++;
                    if ($maxMessages > 0 && $msgCount >= $maxMessages) {
                        $consumer->unsubscribe();
                        return;
                    }
                    break;

                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    // No messages available – loop again.
                    break;

                default:
                    $this->logger->error('[Kafka] Consumer error', [
                        'error' => $message->errstr(),
                        'code'  => $message->err,
                    ]);
                    break;
            }
        }
    }

    /**
     * {@inheritDoc}
     *
     * Publishes all messages then flushes the producer.
     */
    public function publishBatch(string $topic, array $messages): bool
    {
        $producer      = $this->getProducer();
        $producerTopic = $this->getProducerTopic($producer, $topic);

        foreach ($messages as $index => $message) {
            $payload = json_encode($message, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
            $producerTopic->producev(
                partition: RD_KAFKA_PARTITION_UA,
                msgflags: 0,
                payload: $payload,
                key: null,
                headers: [
                    'message_id' => (string) \Illuminate\Support\Str::uuid(),
                    'batch_seq'  => (string) $index,
                ],
            );
            $producer->poll(0);
        }

        $result = $producer->flush(self::FLUSH_TIMEOUT_MS);
        if ($result !== RD_KAFKA_RESP_ERR_NO_ERROR) {
            throw new \RuntimeException(
                '[Kafka] Batch flush failed: ' . rd_kafka_err2str($result)
            );
        }

        $this->logger->debug('[Kafka] Batch published', [
            'topic' => $topic,
            'count' => count($messages),
        ]);

        return true;
    }

    /**
     * {@inheritDoc}
     *
     * Commits the offset of the acknowledged message.
     */
    public function acknowledge(mixed $message): void
    {
        /** @var Message $message */
        // Find the consumer that owns this message's topic/partition and commit.
        $topicKey = $message->topic_name . '_' . $this->groupId;

        if (isset($this->consumers[$topicKey])) {
            $this->consumers[$topicKey]->commit($message);
        }
    }

    /**
     * {@inheritDoc}
     *
     * Kafka has no native nack; rejection either seeks back (requeue=true)
     * or simply skips the message by committing (requeue=false).
     */
    public function reject(mixed $message, bool $requeue = false): void
    {
        /** @var Message $message */
        if (!$requeue) {
            // Commit to advance past the bad message.
            $this->acknowledge($message);
            return;
        }

        $topicKey = $message->topic_name . '_' . $this->groupId;
        if (isset($this->consumers[$topicKey])) {
            // Seek back to the offset to reprocess.
            $topicPartition = new \RdKafka\TopicPartition(
                $message->topic_name,
                $message->partition,
                $message->offset,
            );
            $this->consumers[$topicKey]->seek($topicPartition, 5000);
        }
    }

    /** {@inheritDoc} */
    public function getConnectionStatus(): array
    {
        $start = microtime(true);

        try {
            $producer = $this->getProducer();
            $metadata = $producer->getMetadata(all_topics: true, only_topic: null, timeout_ms: self::METADATA_TIMEOUT);
            $latency  = (microtime(true) - $start) * 1000;

            return [
                'connected'  => true,
                'driver'     => 'kafka',
                'host'       => $this->brokers,
                'latency_ms' => round($latency, 2),
                'details'    => [
                    'group_id'   => $this->groupId,
                    'broker_ct'  => count($metadata->getBrokers()),
                    'topic_ct'   => count($metadata->getTopics()),
                ],
            ];
        } catch (\Throwable $e) {
            return [
                'connected'  => false,
                'driver'     => 'kafka',
                'host'       => $this->brokers,
                'latency_ms' => 0.0,
                'details'    => ['error' => $e->getMessage()],
            ];
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Internal helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function getProducer(): Producer
    {
        if ($this->producer === null) {
            $conf = $this->buildBaseConf();
            $conf->set('enable.idempotence', 'true');
            $conf->set('acks', 'all');
            $conf->set('retries', '5');
            $conf->set('delivery.report.only.error', 'false');

            $conf->setDrMsgCb(function (Producer $kafka, Message $message): void {
                if ($message->err !== RD_KAFKA_RESP_ERR_NO_ERROR) {
                    $this->logger->error('[Kafka] Delivery failed', [
                        'error' => $message->errstr(),
                        'topic' => $message->topic_name,
                    ]);
                }
            });

            $this->producer = new Producer($conf);
        }

        return $this->producer;
    }

    private function getProducerTopic(Producer $producer, string $topic): ProducerTopic
    {
        if (!isset($this->producerTopics[$topic])) {
            $this->producerTopics[$topic] = $producer->newTopic($topic);
        }

        return $this->producerTopics[$topic];
    }

    private function buildConsumer(): KafkaConsumer
    {
        $conf = $this->buildBaseConf();
        $conf->set('group.id', $this->groupId);
        $conf->set('auto.offset.reset', $this->autoOffsetReset);
        $conf->set('enable.auto.commit', 'false');
        $conf->set('session.timeout.ms', (string) $this->sessionTimeoutMs);
        $conf->set('max.poll.interval.ms', (string) ($this->sessionTimeoutMs * 3));

        $conf->setRebalanceCb(function (KafkaConsumer $kafka, int $err, ?array $partitions = null): void {
            match ($err) {
                RD_KAFKA_RESP_ERR__ASSIGN_PARTITIONS => $kafka->assign($partitions),
                RD_KAFKA_RESP_ERR__REVOKE_PARTITIONS => $kafka->assign(null),
                default => $this->logger->warning('[Kafka] Unexpected rebalance error', ['code' => $err]),
            };
        });

        return new KafkaConsumer($conf);
    }

    private function buildBaseConf(): Conf
    {
        $conf = new Conf();
        $conf->set('metadata.broker.list', $this->brokers);
        $conf->set('security.protocol', $this->securityProto);

        if (!in_array($this->securityProto, ['PLAINTEXT', 'SSL'], strict: true)) {
            $conf->set('sasl.mechanisms', $this->saslMechanism);
            $conf->set('sasl.username', $this->saslUsername);
            $conf->set('sasl.password', $this->saslPassword);
        }

        $conf->setLogCb(function ($kafka, int $level, string $facility, string $message): void {
            $this->logger->debug("[Kafka][{$facility}] {$message}", ['level' => $level]);
        });

        return $conf;
    }
}

<?php

declare(strict_types=1);

namespace App\Shared\Messaging;

use App\Shared\Contracts\MessageBrokerInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPConnectionClosedException;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * RabbitMQ Message Broker.
 *
 * Full implementation of {@see MessageBrokerInterface} backed by php-amqplib.
 * Features:
 *  - Automatic reconnection with exponential back-off
 *  - Publisher confirms (channel in confirm mode)
 *  - Dead-letter exchange support via message TTL options
 *  - Batch publish using channel transactions
 *  - Per-consumer channel isolation
 */
final class RabbitMQBroker implements MessageBrokerInterface
{
    private ?AMQPStreamConnection $connection = null;

    /** Channels keyed by a usage label (e.g. 'publish', 'consumer_{queue}'). */
    private array $channels = [];

    /** Connection creation timestamp for latency tracking. */
    private float $connectedAt = 0.0;

    private const int MAX_RECONNECT_ATTEMPTS = 5;
    private const int RECONNECT_BASE_DELAY_MS = 500;

    /**
     * @param  string          $host
     * @param  int             $port
     * @param  string          $user
     * @param  string          $password
     * @param  string          $vhost
     * @param  string          $exchange        Default exchange name.
     * @param  string          $exchangeType    fanout|direct|topic|headers
     * @param  bool            $publisherConfirm Whether to enable publisher confirms.
     * @param  int             $connectionTimeout Seconds.
     * @param  LoggerInterface $logger
     */
    public function __construct(
        private readonly string $host,
        private readonly int $port,
        private readonly string $user,
        private readonly string $password,
        private readonly string $vhost = '/',
        private readonly string $exchange = '',
        private readonly string $exchangeType = 'topic',
        private readonly bool $publisherConfirm = true,
        private readonly int $connectionTimeout = 5,
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    // MessageBrokerInterface
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * {@inheritDoc}
     *
     * Options:
     *  - routing_key (string)   AMQP routing key; defaults to $topic.
     *  - headers     (array)    Additional AMQP headers.
     *  - priority    (int)      Message priority (0–9).
     *  - delay_ms    (int)      Delay via x-delay header (requires delayed-message plugin).
     *  - persistent  (bool)     Persistent delivery mode (default: true).
     */
    public function publish(string $topic, array $message, array $options = []): bool
    {
        $channel    = $this->getPublishChannel();
        $routingKey = $options['routing_key'] ?? $topic;
        $exchange   = $options['exchange'] ?? $this->exchange;

        $headers = $options['headers'] ?? [];
        if (isset($options['delay_ms'])) {
            $headers['x-delay'] = (int) $options['delay_ms'];
        }

        $properties = [
            'delivery_mode'   => $options['persistent'] ?? true ? AMQPMessage::DELIVERY_MODE_PERSISTENT : AMQPMessage::DELIVERY_MODE_NON_PERSISTENT,
            'content_type'    => 'application/json',
            'message_id'      => $options['message_id'] ?? (string) \Illuminate\Support\Str::uuid(),
            'timestamp'       => time(),
            'app_id'          => config('app.name', 'kv-saas'),
        ];

        if (isset($options['priority'])) {
            $properties['priority'] = (int) $options['priority'];
        }

        if (!empty($headers)) {
            $properties['application_headers'] = new AMQPTable($headers);
        }

        $amqpMessage = new AMQPMessage(
            body: json_encode($message, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            properties: $properties,
        );

        $channel->basic_publish($amqpMessage, $exchange, $routingKey);

        if ($this->publisherConfirm) {
            $channel->wait_for_pending_acks(timeout: 5.0);
        }

        $this->logger->debug('[RabbitMQ] Message published', [
            'exchange'    => $exchange,
            'routing_key' => $routingKey,
            'message_id'  => $properties['message_id'],
        ]);

        return true;
    }

    /**
     * {@inheritDoc}
     *
     * Blocks and processes messages until the $handler returns false or
     * options['max_messages'] is reached.
     *
     * Options:
     *  - consumer_tag  (string)
     *  - no_ack        (bool)    Default false (manual ack).
     *  - exclusive     (bool)    Default false.
     *  - prefetch      (int)     QoS prefetch count; default 10.
     *  - max_messages  (int|0)   Stop after N messages (0 = infinite).
     *  - timeout       (float)   Channel wait timeout in seconds.
     */
    public function subscribe(string $topic, callable $handler, array $options = []): void
    {
        $channel = $this->getChannel("consumer_{$topic}");

        $prefetch = (int) ($options['prefetch'] ?? 10);
        $channel->basic_qos(prefetch_size: 0, prefetch_count: $prefetch, a_global: false);

        $noAck       = (bool) ($options['no_ack'] ?? false);
        $exclusive   = (bool) ($options['exclusive'] ?? false);
        $consumerTag = $options['consumer_tag'] ?? '';
        $maxMessages = (int) ($options['max_messages'] ?? 0);
        $timeout     = (float) ($options['timeout'] ?? 0);
        $msgCount    = 0;

        $callback = function (AMQPMessage $msg) use ($handler, $noAck, $maxMessages, &$msgCount): void {
            try {
                $decoded = json_decode($msg->body, associative: true, flags: JSON_THROW_ON_ERROR);
                $handler($msg, $decoded);
            } catch (\Throwable $e) {
                $this->logger->error('[RabbitMQ] Handler exception', [
                    'error' => $e->getMessage(),
                ]);

                if (!$noAck) {
                    $this->reject($msg, requeue: false);
                }

                return;
            }

            $msgCount++;

            if ($maxMessages > 0 && $msgCount >= $maxMessages) {
                $msg->getChannel()->basic_cancel($msg->getConsumerTag());
            }
        };

        $channel->basic_consume(
            queue: $topic,
            consumer_tag: $consumerTag,
            no_local: false,
            no_ack: $noAck,
            exclusive: $exclusive,
            nowait: false,
            callback: $callback,
        );

        while ($channel->is_consuming()) {
            $channel->wait(allowed_methods: null, non_blocking: false, timeout: $timeout);
        }
    }

    /**
     * {@inheritDoc}
     *
     * Uses a channel transaction to batch-publish atomically.
     */
    public function publishBatch(string $topic, array $messages): bool
    {
        $channel = $this->getPublishChannel();

        $channel->tx_select();

        try {
            foreach ($messages as $message) {
                $this->publish($topic, $message);
            }
            $channel->tx_commit();
        } catch (\Throwable $e) {
            $channel->tx_rollback();
            $this->logger->error('[RabbitMQ] Batch publish rolled back', ['error' => $e->getMessage()]);
            throw $e;
        }

        return true;
    }

    /** {@inheritDoc} */
    public function acknowledge(mixed $message): void
    {
        /** @var AMQPMessage $message */
        $message->ack();
    }

    /** {@inheritDoc} */
    public function reject(mixed $message, bool $requeue = false): void
    {
        /** @var AMQPMessage $message */
        $message->nack(requeue: $requeue);
    }

    /** {@inheritDoc} */
    public function getConnectionStatus(): array
    {
        $start = microtime(true);

        try {
            $this->ensureConnected();
            $isConnected = $this->connection?->isConnected() ?? false;
            $latency     = (microtime(true) - $start) * 1000;
        } catch (\Throwable $e) {
            return [
                'connected'  => false,
                'driver'     => 'rabbitmq',
                'host'       => $this->host,
                'latency_ms' => 0.0,
                'details'    => ['error' => $e->getMessage()],
            ];
        }

        return [
            'connected'  => $isConnected,
            'driver'     => 'rabbitmq',
            'host'       => $this->host,
            'latency_ms' => round($latency, 2),
            'details'    => [
                'vhost'      => $this->vhost,
                'exchange'   => $this->exchange,
                'channel_ct' => count($this->channels),
            ],
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Exchange / Queue helpers (public for DI consumers)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Declare an exchange idempotently.
     *
     * @param  string  $exchange
     * @param  string  $type     direct|fanout|topic|headers
     * @param  bool    $durable
     * @return void
     */
    public function declareExchange(
        string $exchange,
        string $type = 'topic',
        bool $durable = true,
    ): void {
        $this->getPublishChannel()->exchange_declare(
            exchange: $exchange,
            type: $type,
            passive: false,
            durable: $durable,
            auto_delete: false,
        );
    }

    /**
     * Declare a queue with optional dead-letter routing.
     *
     * @param  string       $queue
     * @param  bool         $durable
     * @param  string|null  $dlx     Dead-letter exchange.
     * @param  int|null     $ttl     Message TTL in ms.
     * @return void
     */
    public function declareQueue(
        string $queue,
        bool $durable = true,
        ?string $dlx = null,
        ?int $ttl = null,
    ): void {
        $args = [];
        if ($dlx !== null) {
            $args['x-dead-letter-exchange'] = ['S', $dlx];
        }
        if ($ttl !== null) {
            $args['x-message-ttl'] = ['I', $ttl];
        }

        $this->getPublishChannel()->queue_declare(
            queue: $queue,
            passive: false,
            durable: $durable,
            exclusive: false,
            auto_delete: false,
            nowait: false,
            arguments: empty($args) ? [] : new AMQPTable($args),
        );
    }

    /**
     * Bind a queue to an exchange with a routing key.
     */
    public function bindQueue(string $queue, string $exchange, string $routingKey): void
    {
        $this->getPublishChannel()->queue_bind($queue, $exchange, $routingKey);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Connection / Channel management
    // ─────────────────────────────────────────────────────────────────────────

    private function getPublishChannel(): AMQPChannel
    {
        return $this->getChannel('publish');
    }

    private function getChannel(string $label): AMQPChannel
    {
        if (!isset($this->channels[$label]) || !$this->channels[$label]->is_open()) {
            $this->ensureConnected();
            $channel = $this->connection->channel();

            if ($label === 'publish' && $this->publisherConfirm) {
                $channel->confirm_select();
            }

            $this->channels[$label] = $channel;
        }

        return $this->channels[$label];
    }

    private function ensureConnected(): void
    {
        if ($this->connection !== null && $this->connection->isConnected()) {
            return;
        }

        $this->connect();
    }

    private function connect(int $attempt = 1): void
    {
        try {
            $this->connection = new AMQPStreamConnection(
                host: $this->host,
                port: $this->port,
                user: $this->user,
                password: $this->password,
                vhost: $this->vhost,
                connection_timeout: $this->connectionTimeout,
                read_write_timeout: $this->connectionTimeout * 2,
                heartbeat: 60,
            );

            $this->connectedAt = microtime(true);
            $this->channels    = [];

            $this->logger->info('[RabbitMQ] Connected', [
                'host'  => $this->host,
                'vhost' => $this->vhost,
            ]);
        } catch (\Throwable $e) {
            if ($attempt >= self::MAX_RECONNECT_ATTEMPTS) {
                throw new \RuntimeException(
                    "[RabbitMQ] Could not connect after {$attempt} attempts: {$e->getMessage()}",
                    previous: $e,
                );
            }

            $delayMs = self::RECONNECT_BASE_DELAY_MS * (2 ** ($attempt - 1));

            $this->logger->warning('[RabbitMQ] Connection failed, retrying', [
                'attempt'  => $attempt,
                'delay_ms' => $delayMs,
                'error'    => $e->getMessage(),
            ]);

            usleep($delayMs * 1000);
            $this->connect($attempt + 1);
        }
    }

    public function __destruct()
    {
        foreach ($this->channels as $channel) {
            try {
                $channel->close();
            } catch (\Throwable) {
                // Ignore errors on shutdown.
            }
        }

        try {
            $this->connection?->close();
        } catch (\Throwable) {
            // Ignore errors on shutdown.
        }
    }
}

<?php

namespace App\Infrastructure\Messaging\RabbitMQ;

use App\Infrastructure\Messaging\Contracts\MessageBrokerInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Exception\AMQPConnectionClosedException;
use PhpAmqpLib\Exception\AMQPIOException;
use Illuminate\Support\Facades\Log;

class RabbitMQMessageBroker implements MessageBrokerInterface
{
    private ?AMQPStreamConnection $connection = null;
    private ?AMQPChannel $channel = null;

    private string $host;
    private int    $port;
    private string $user;
    private string $password;
    private string $vhost;
    private int    $maxRetries;
    private int    $retryDelay; // seconds

    public function __construct(array $config = [])
    {
        $this->host       = $config['host']        ?? config('messaging.rabbitmq.host',        'rabbitmq');
        $this->port       = (int) ($config['port'] ?? config('messaging.rabbitmq.port',        5672));
        $this->user       = $config['user']        ?? config('messaging.rabbitmq.user',        'guest');
        $this->password   = $config['password']    ?? config('messaging.rabbitmq.password',    'guest');
        $this->vhost      = $config['vhost']       ?? config('messaging.rabbitmq.vhost',       '/');
        $this->maxRetries = (int) ($config['max_retries'] ?? 3);
        $this->retryDelay = (int) ($config['retry_delay'] ?? 2);
    }

    // ─── Connection management ────────────────────────────────────────────────

    private function connect(): void
    {
        $attempt = 0;

        while ($attempt < $this->maxRetries) {
            try {
                $this->connection = new AMQPStreamConnection(
                    $this->host,
                    $this->port,
                    $this->user,
                    $this->password,
                    $this->vhost,
                    false,   // insist
                    'AMQPLAIN',
                    null,
                    'en_US',
                    10.0,    // connection_timeout
                    10.0,    // read_write_timeout
                    null,
                    false,   // keepalive
                    60       // heartbeat
                );

                $this->channel = $this->connection->channel();
                Log::info('[RabbitMQ] Connected successfully.');

                return;
            } catch (\Exception $e) {
                $attempt++;
                Log::warning("[RabbitMQ] Connection attempt {$attempt} failed: {$e->getMessage()}");

                if ($attempt >= $this->maxRetries) {
                    throw new \RuntimeException(
                        "RabbitMQ: unable to connect after {$this->maxRetries} attempts. Last error: {$e->getMessage()}",
                        0,
                        $e
                    );
                }

                sleep($this->retryDelay);
            }
        }
    }

    private function ensureConnected(): void
    {
        if (! $this->isConnected()) {
            $this->connect();
        }
    }

    private function reconnect(): void
    {
        $this->disconnect();
        $this->connect();
    }

    // ─── Exchange / queue helpers ─────────────────────────────────────────────

    /**
     * Declare a topic exchange (idempotent – safe to call repeatedly).
     */
    private function declareExchange(string $exchange, string $type = 'topic'): void
    {
        $this->channel->exchange_declare(
            $exchange,
            $type,
            false,  // passive
            true,   // durable
            false   // auto_delete
        );
    }

    /**
     * Declare a durable queue and optionally bind it to an exchange.
     */
    private function declareQueue(string $queue, string $exchange = '', string $routingKey = ''): void
    {
        $this->channel->queue_declare(
            $queue,
            false,  // passive
            true,   // durable
            false,  // exclusive
            false   // auto_delete
        );

        if ($exchange !== '') {
            $this->channel->queue_bind($queue, $exchange, $routingKey ?: $queue);
        }
    }

    // ─── Interface implementation ─────────────────────────────────────────────

    public function publish(string $topic, array $message, array $options = []): void
    {
        $this->ensureConnected();

        $exchange   = $options['exchange']    ?? $topic;
        $routingKey = $options['routing_key'] ?? $topic;
        $type       = $options['exchange_type'] ?? 'topic';

        try {
            $this->declareExchange($exchange, $type);

            $body    = json_encode($message, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
            $headers = array_merge(
                ['content_type' => 'application/json', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT],
                $options['headers'] ?? []
            );

            $amqpMessage = new AMQPMessage($body, $headers);
            $this->channel->basic_publish($amqpMessage, $exchange, $routingKey);

            Log::debug("[RabbitMQ] Published to exchange={$exchange} routing_key={$routingKey}");
        } catch (AMQPConnectionClosedException | AMQPIOException $e) {
            Log::warning("[RabbitMQ] Connection lost during publish, reconnecting…");
            $this->reconnect();
            // Retry once after reconnection
            $this->publish($topic, $message, $options);
        }
    }

    public function subscribe(string $topic, callable $handler, array $options = []): void
    {
        $this->ensureConnected();

        $queue      = $options['queue']       ?? $topic;
        $exchange   = $options['exchange']    ?? $topic;
        $routingKey = $options['routing_key'] ?? '#';
        $prefetch   = (int) ($options['prefetch_count'] ?? 1);
        $type       = $options['exchange_type'] ?? 'topic';

        $this->declareExchange($exchange, $type);
        $this->declareQueue($queue, $exchange, $routingKey);

        $this->channel->basic_qos(0, $prefetch, false);

        $callback = function (AMQPMessage $msg) use ($handler): void {
            try {
                $payload = json_decode($msg->getBody(), true, 512, JSON_THROW_ON_ERROR);
                $handler($payload, $msg);
            } catch (\Throwable $e) {
                Log::error("[RabbitMQ] Handler exception: {$e->getMessage()}", ['body' => $msg->getBody()]);
                $this->reject($msg, false);
            }
        };

        $this->channel->basic_consume(
            $queue,
            '',    // consumer tag – auto-generated
            false, // no_local
            false, // no_ack (we ack manually)
            false, // exclusive
            false, // nowait
            $callback
        );

        Log::info("[RabbitMQ] Subscribed to queue={$queue}");

        // Block until the channel has no more callbacks
        while ($this->channel->is_consuming()) {
            try {
                $this->channel->wait();
            } catch (AMQPConnectionClosedException | AMQPIOException $e) {
                Log::warning("[RabbitMQ] Connection lost during consume, reconnecting…");
                $this->reconnect();
                $this->subscribe($topic, $handler, $options);
                return;
            }
        }
    }

    public function acknowledge(mixed $message): void
    {
        if ($message instanceof AMQPMessage) {
            $message->ack();
        }
    }

    public function reject(mixed $message, bool $requeue = false): void
    {
        if ($message instanceof AMQPMessage) {
            $message->reject($requeue);
        }
    }

    public function disconnect(): void
    {
        try {
            if ($this->channel !== null) {
                $this->channel->close();
            }
        } catch (\Throwable) {
        } finally {
            $this->channel = null;
        }

        try {
            if ($this->connection !== null) {
                $this->connection->close();
            }
        } catch (\Throwable) {
        } finally {
            $this->connection = null;
        }
    }

    public function isConnected(): bool
    {
        return $this->connection !== null
            && $this->connection->isConnected()
            && $this->channel !== null
            && $this->channel->is_open();
    }

    public function __destruct()
    {
        $this->disconnect();
    }
}

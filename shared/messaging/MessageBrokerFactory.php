<?php

declare(strict_types=1);

namespace App\Shared\Messaging;

use App\Shared\Contracts\MessageBrokerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Message Broker Factory.
 *
 * Resolves and constructs the correct {@see MessageBrokerInterface}
 * implementation based on a driver name or the MESSAGE_BROKER_DRIVER
 * environment variable.
 *
 * Usage:
 *   $broker = MessageBrokerFactory::make('rabbitmq', $config);
 *   // or via DI container binding:
 *   $broker = app(MessageBrokerFactory::class)->make();
 */
final class MessageBrokerFactory
{
    public const string DRIVER_RABBITMQ = 'rabbitmq';
    public const string DRIVER_KAFKA    = 'kafka';

    private static array $instances = [];

    public function __construct(
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {}

    /**
     * Build (or retrieve cached) a broker instance for the given driver.
     *
     * @param  string|null         $driver  'rabbitmq'|'kafka'; falls back to env MESSAGE_BROKER_DRIVER.
     * @param  array<string,mixed> $config  Driver-specific config overrides.
     * @return MessageBrokerInterface
     *
     * @throws \InvalidArgumentException  For unsupported driver names.
     */
    public function make(
        ?string $driver = null,
        array $config = [],
    ): MessageBrokerInterface {
        $driver = strtolower($driver ?? config('messaging.driver', env('MESSAGE_BROKER_DRIVER', self::DRIVER_RABBITMQ)));

        $cacheKey = $driver . '_' . md5(serialize($config));

        if (isset(self::$instances[$cacheKey])) {
            return self::$instances[$cacheKey];
        }

        $broker = match ($driver) {
            self::DRIVER_RABBITMQ => $this->buildRabbitMQ($config),
            self::DRIVER_KAFKA    => $this->buildKafka($config),
            default               => throw new \InvalidArgumentException(
                "Unsupported message broker driver [{$driver}]. "
                . 'Supported: rabbitmq, kafka.'
            ),
        };

        self::$instances[$cacheKey] = $broker;

        return $broker;
    }

    /**
     * Convenience static factory when the service container is not available.
     *
     * @param  string|null         $driver
     * @param  array<string,mixed> $config
     * @return MessageBrokerInterface
     */
    public static function create(?string $driver = null, array $config = []): MessageBrokerInterface
    {
        return (new self())->make($driver, $config);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private builders
    // ─────────────────────────────────────────────────────────────────────────

    private function buildRabbitMQ(array $config): RabbitMQBroker
    {
        return new RabbitMQBroker(
            host:             $config['host']             ?? config('messaging.rabbitmq.host', env('RABBITMQ_HOST', 'rabbitmq')),
            port:             (int) ($config['port']      ?? config('messaging.rabbitmq.port', env('RABBITMQ_PORT', 5672))),
            user:             $config['user']             ?? config('messaging.rabbitmq.user', env('RABBITMQ_USER', 'guest')),
            password:         $config['password']         ?? config('messaging.rabbitmq.password', env('RABBITMQ_PASSWORD', 'guest')),
            vhost:            $config['vhost']            ?? config('messaging.rabbitmq.vhost', env('RABBITMQ_VHOST', '/')),
            exchange:         $config['exchange']         ?? config('messaging.rabbitmq.exchange', env('RABBITMQ_EXCHANGE', 'kv.saas.events')),
            exchangeType:     $config['exchange_type']    ?? config('messaging.rabbitmq.exchange_type', 'topic'),
            publisherConfirm: (bool) ($config['publisher_confirm'] ?? true),
            logger:           $this->logger,
        );
    }

    private function buildKafka(array $config): KafkaBroker
    {
        return new KafkaBroker(
            brokers:          $config['brokers']           ?? config('messaging.kafka.brokers', env('KAFKA_BROKER', 'kafka:9092')),
            groupId:          $config['group_id']          ?? config('messaging.kafka.group_id', env('KAFKA_GROUP_ID', 'kv-saas-consumers')),
            securityProto:    $config['security_protocol'] ?? config('messaging.kafka.security_protocol', env('KAFKA_SECURITY_PROTOCOL', 'PLAINTEXT')),
            saslMechanism:    $config['sasl_mechanism']    ?? config('messaging.kafka.sasl_mechanism', env('KAFKA_SASL_MECHANISM', 'PLAIN')),
            saslUsername:     $config['sasl_username']     ?? config('messaging.kafka.sasl_username', env('KAFKA_SASL_USERNAME', '')),
            saslPassword:     $config['sasl_password']     ?? config('messaging.kafka.sasl_password', env('KAFKA_SASL_PASSWORD', '')),
            autoOffsetReset:  $config['auto_offset_reset'] ?? config('messaging.kafka.auto_offset_reset', env('KAFKA_AUTO_OFFSET_RESET', 'earliest')),
            sessionTimeoutMs: (int) ($config['session_timeout_ms'] ?? config('messaging.kafka.session_timeout_ms', env('KAFKA_SESSION_TIMEOUT_MS', 45000))),
            logger:           $this->logger,
        );
    }
}

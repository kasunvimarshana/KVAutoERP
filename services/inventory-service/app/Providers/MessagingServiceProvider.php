<?php

namespace App\Providers;

use App\Infrastructure\Messaging\Contracts\MessageBrokerInterface;
use App\Infrastructure\Messaging\Kafka\KafkaMessageBroker;
use App\Infrastructure\Messaging\RabbitMQ\RabbitMQMessageBroker;
use Illuminate\Support\ServiceProvider;

class MessagingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MessageBrokerInterface::class, function ($app) {
            $broker = config('messaging.default', 'rabbitmq');

            return match ($broker) {
                'kafka' => new KafkaMessageBroker([
                    'brokers' => config('messaging.kafka.brokers'),
                ]),
                default => new RabbitMQMessageBroker([
                    'host'       => config('messaging.rabbitmq.host'),
                    'port'       => config('messaging.rabbitmq.port'),
                    'user'       => config('messaging.rabbitmq.user'),
                    'password'   => config('messaging.rabbitmq.password'),
                    'vhost'      => config('messaging.rabbitmq.vhost'),
                ]),
            };
        });
    }
}

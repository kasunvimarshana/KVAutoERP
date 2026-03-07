<?php

namespace App\Core\MessageBroker;

use Illuminate\Support\Facades\Log;

/**
 * Apache Kafka broker implementation.
 *
 * Install php-rdkafka to enable actual connectivity:
 *   composer require arnaud-lb/php-rdkafka
 *
 * Then uncomment the constructor body and the publish/subscribe logic.
 */
class KafkaMessageBroker implements MessageBrokerInterface
{
    public function publish(string $topic, array $message): void
    {
        Log::info("Kafka publish to '{$topic}'", $message);
        // $conf = new \RdKafka\Conf();
        // $conf->set('metadata.broker.list', config('messagebroker.kafka.brokers'));
        // $producer = new \RdKafka\Producer($conf);
        // $topic = $producer->newTopic($topic);
        // $topic->produce(RD_KAFKA_PARTITION_UA, 0, json_encode($message));
        // $producer->flush(10_000);
    }

    public function subscribe(string $topic, callable $handler): void
    {
        Log::info("Kafka subscribe to '{$topic}'");
        // $conf = new \RdKafka\Conf();
        // $conf->set('group.id', config('messagebroker.kafka.group_id'));
        // $conf->set('metadata.broker.list', config('messagebroker.kafka.brokers'));
        // $consumer = new \RdKafka\KafkaConsumer($conf);
        // $consumer->subscribe([$topic]);
        // while (true) {
        //     $message = $consumer->consume(120 * 1000);
        //     if ($message->err === RD_KAFKA_RESP_ERR_NO_ERROR) { $handler($message); }
        // }
    }

    public function acknowledge(string $messageId): void
    {
        Log::info("Kafka acknowledge '{$messageId}'");
    }
}

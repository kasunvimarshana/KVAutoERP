<?php

namespace Shared\Core\Contracts;

interface MessageBrokerInterface
{
    /**
     * Publish a message to a topic/queue
     *
     * @param string $topic
     * @param array $message
     * @return void
     */
    public function publish(string $topic, array $message): void;

    /**
     * Subscribe to a topic/queue
     *
     * @param string $topic
     * @param callable $callback
     * @return void
     */
    public function subscribe(string $topic, callable $callback): void;
}

<?php

namespace App\Shared\MessageBroker\Drivers;

use App\Shared\MessageBroker\Contracts\MessageBrokerInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Database-backed message broker for development/testing.
 * Stores messages in a `broker_messages` table.
 */
class DatabaseBroker implements MessageBrokerInterface
{
    private bool $connected = true;

    public function publish(string $topic, array $message, array $options = []): bool
    {
        DB::table('broker_messages')->insert([
            'id'         => Str::uuid()->toString(),
            'topic'      => $topic,
            'payload'    => json_encode($message),
            'status'     => 'pending',
            'attempts'   => 0,
            'options'    => json_encode($options),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return true;
    }

    public function subscribe(string $topic, callable $handler, array $options = []): void
    {
        $limit = $options['limit'] ?? 100;
        $messages = DB::table('broker_messages')
            ->where('topic', $topic)
            ->where('status', 'pending')
            ->orderBy('created_at')
            ->limit($limit)
            ->get();

        foreach ($messages as $message) {
            try {
                DB::table('broker_messages')
                    ->where('id', $message->id)
                    ->update(['status' => 'processing', 'updated_at' => now()]);

                $handler(json_decode($message->payload, true), $message->id);

                DB::table('broker_messages')
                    ->where('id', $message->id)
                    ->update(['status' => 'processed', 'updated_at' => now()]);
            } catch (\Throwable $e) {
                $attempts = $message->attempts + 1;
                DB::table('broker_messages')
                    ->where('id', $message->id)
                    ->update([
                        'status'     => $attempts >= 3 ? 'failed' : 'pending',
                        'attempts'   => $attempts,
                        'error'      => $e->getMessage(),
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    public function publishBatch(string $topic, array $messages): bool
    {
        $rows = array_map(fn($msg) => [
            'id'         => Str::uuid()->toString(),
            'topic'      => $topic,
            'payload'    => json_encode($msg),
            'status'     => 'pending',
            'attempts'   => 0,
            'options'    => '{}',
            'created_at' => now(),
            'updated_at' => now(),
        ], $messages);

        DB::table('broker_messages')->insert($rows);
        return true;
    }

    public function ack(string $messageId): void
    {
        DB::table('broker_messages')
            ->where('id', $messageId)
            ->update(['status' => 'processed', 'updated_at' => now()]);
    }

    public function nack(string $messageId, bool $requeue = true): void
    {
        DB::table('broker_messages')
            ->where('id', $messageId)
            ->update([
                'status'     => $requeue ? 'pending' : 'failed',
                'updated_at' => now(),
            ]);
    }

    public function isConnected(): bool
    {
        try {
            DB::connection()->getPdo();
            return $this->connected;
        } catch (\Throwable) {
            return false;
        }
    }

    public function disconnect(): void
    {
        $this->connected = false;
    }
}

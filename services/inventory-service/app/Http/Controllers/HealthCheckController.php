<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class HealthCheckController extends Controller
{
    public function check(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'redis'    => $this->checkRedis(),
            'rabbitmq' => $this->checkRabbitMQ(),
        ];

        $allHealthy = collect($checks)->every(fn ($c) => $c['status'] === 'ok');

        return response()->json([
            'status'  => $allHealthy ? 'healthy' : 'degraded',
            'service' => config('app.name', 'inventory-service'),
            'checks'  => $checks,
            'time'    => now()->toISOString(),
        ], $allHealthy ? 200 : 503);
    }

    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            DB::select('SELECT 1');

            return ['status' => 'ok', 'message' => 'Database connection is healthy.'];
        } catch (\Throwable $e) {
            return ['status' => 'fail', 'message' => 'Database unreachable: ' . $e->getMessage()];
        }
    }

    private function checkRedis(): array
    {
        try {
            $pong = Redis::ping();

            if ($pong === 'PONG' || $pong === true || $pong === 1) {
                return ['status' => 'ok', 'message' => 'Redis connection is healthy.'];
            }

            return ['status' => 'fail', 'message' => 'Redis ping returned unexpected response.'];
        } catch (\Throwable $e) {
            return ['status' => 'fail', 'message' => 'Redis unreachable: ' . $e->getMessage()];
        }
    }

    private function checkRabbitMQ(): array
    {
        try {
            $connection = new AMQPStreamConnection(
                config('messaging.rabbitmq.host', 'rabbitmq'),
                (int) config('messaging.rabbitmq.port', 5672),
                config('messaging.rabbitmq.user', 'guest'),
                config('messaging.rabbitmq.password', 'guest'),
                config('messaging.rabbitmq.vhost', '/'),
                false,
                'AMQPLAIN',
                null,
                'en_US',
                3.0,   // connection_timeout
                3.0    // read_write_timeout
            );

            $connection->close();

            return ['status' => 'ok', 'message' => 'RabbitMQ connection is healthy.'];
        } catch (\Throwable $e) {
            return ['status' => 'fail', 'message' => 'RabbitMQ unreachable: ' . $e->getMessage()];
        }
    }
}

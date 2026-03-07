<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class HealthCheckController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database'  => $this->checkDatabase(),
            'redis'     => $this->checkRedis(),
            'rabbitmq'  => $this->checkRabbitMQ(),
        ];

        $healthy    = !in_array(false, array_column($checks, 'healthy'), true);
        $statusCode = $healthy ? 200 : 503;

        return response()->json([
            'service' => config('app.name', 'OrderService'),
            'status'  => $healthy ? 'healthy' : 'degraded',
            'checks'  => $checks,
            'timestamp' => now()->toIso8601String(),
        ], $statusCode);
    }

    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            DB::select('SELECT 1');
            return ['healthy' => true, 'message' => 'Connected'];
        } catch (\Throwable $e) {
            return ['healthy' => false, 'message' => $e->getMessage()];
        }
    }

    private function checkRedis(): array
    {
        try {
            Redis::ping();
            return ['healthy' => true, 'message' => 'Connected'];
        } catch (\Throwable $e) {
            return ['healthy' => false, 'message' => $e->getMessage()];
        }
    }

    private function checkRabbitMQ(): array
    {
        try {
            $connection = new AMQPStreamConnection(
                config('rabbitmq.host',     env('RABBITMQ_HOST', 'rabbitmq')),
                config('rabbitmq.port',     env('RABBITMQ_PORT', 5672)),
                config('rabbitmq.user',     env('RABBITMQ_USER', 'guest')),
                config('rabbitmq.password', env('RABBITMQ_PASSWORD', 'guest')),
                config('rabbitmq.vhost',    env('RABBITMQ_VHOST', '/'))
            );
            $connection->close();
            return ['healthy' => true, 'message' => 'Connected'];
        } catch (\Throwable $e) {
            return ['healthy' => false, 'message' => $e->getMessage()];
        }
    }
}

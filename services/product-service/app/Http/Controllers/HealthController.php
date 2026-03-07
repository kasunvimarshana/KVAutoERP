<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class HealthController extends Controller
{
    public function check(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache'    => $this->checkCache(),
            'broker'   => $this->checkMessageBroker(),
        ];

        $allHealthy = !in_array('unhealthy', array_column($checks, 'status'));

        return response()->json([
            'status'    => $allHealthy ? 'healthy' : 'degraded',
            'service'   => config('app.name'),
            'version'   => config('app.version', '1.0.0'),
            'checks'    => $checks,
            'timestamp' => now()->toISOString(),
        ], $allHealthy ? 200 : 503);
    }

    private function checkDatabase(): array
    {
        try {
            DB::select('SELECT 1');
            return ['status' => 'healthy', 'message' => 'Database connection successful'];
        } catch (\Exception $e) {
            return ['status' => 'unhealthy', 'message' => $e->getMessage()];
        }
    }

    private function checkCache(): array
    {
        try {
            Cache::set('health_check', true, 5);
            Cache::get('health_check');
            return ['status' => 'healthy', 'message' => 'Cache connection successful'];
        } catch (\Exception $e) {
            return ['status' => 'unhealthy', 'message' => $e->getMessage()];
        }
    }

    private function checkMessageBroker(): array
    {
        try {
            $connection = new \PhpAmqpLib\Connection\AMQPStreamConnection(
                config('rabbitmq.host', 'rabbitmq'),
                config('rabbitmq.port', 5672),
                config('rabbitmq.username', 'guest'),
                config('rabbitmq.password', 'guest'),
                config('rabbitmq.vhost', '/')
            );
            $connection->close();
            return ['status' => 'healthy', 'message' => 'RabbitMQ connection successful'];
        } catch (\Exception $e) {
            // Do not expose internal connection details in responses
            return ['status' => 'degraded', 'message' => 'RabbitMQ unavailable'];
        }
    }
}

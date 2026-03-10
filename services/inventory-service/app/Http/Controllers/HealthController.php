<?php
namespace App\Http\Controllers;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
class HealthController extends Controller
{
    public function check(): JsonResponse
    {
        $checks  = ['database' => $this->checkDatabase(), 'cache' => $this->checkCache()];
        $healthy = collect($checks)->every(fn($c) => $c['status'] === 'ok');
        return response()->json(['service' => 'inventory-service', 'status' => $healthy ? 'healthy' : 'degraded', 'checks' => $checks, 'timestamp' => now()->toIso8601String()], $healthy ? 200 : 503);
    }
    private function checkDatabase(): array { try { DB::select('SELECT 1'); return ['status' => 'ok']; } catch (\Throwable $e) { return ['status' => 'error', 'message' => $e->getMessage()]; } }
    private function checkCache(): array { try { Cache::put('health_check', true, 5); return ['status' => 'ok']; } catch (\Throwable $e) { return ['status' => 'error', 'message' => $e->getMessage()]; } }
}

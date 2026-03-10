<?php
namespace App\Infrastructure;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
class InventoryServiceClient {
    private string $baseUrl;
    public function __construct() { $this->baseUrl = config('services.inventory.url', 'http://inventory_service'); }
    public function reserveStock(string $tenantId, string $productId, int $quantity, string $orderId): array { return $this->post('/api/inventory/reserve', $tenantId, ['product_id'=>$productId,'quantity'=>$quantity,'order_id'=>$orderId]); }
    public function releaseStock(string $tenantId, string $productId, int $quantity, string $orderId): array { return $this->post('/api/inventory/release', $tenantId, ['product_id'=>$productId,'quantity'=>$quantity,'order_id'=>$orderId]); }
    public function confirmStock(string $tenantId, string $productId, int $quantity, string $orderId): array { return $this->post('/api/inventory/confirm', $tenantId, ['product_id'=>$productId,'quantity'=>$quantity,'order_id'=>$orderId]); }
    private function post(string $path, string $tenantId, array $data): array {
        try {
            $response = Http::timeout(10)->withHeaders(['X-Tenant-ID'=>$tenantId])->post("{$this->baseUrl}{$path}", $data);
            return $response->successful() ? ['success'=>true,'data'=>$response->json('data',[])] : ['success'=>false,'message'=>$response->json('message','Inventory service error.')];
        } catch (\Throwable $e) { Log::error('InventoryServiceClient error',['path'=>$path,'error'=>$e->getMessage()]); return ['success'=>false,'message'=>'Inventory service unreachable.']; }
    }
}

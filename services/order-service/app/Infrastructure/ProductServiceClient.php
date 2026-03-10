<?php
namespace App\Infrastructure;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
class ProductServiceClient {
    private string $baseUrl;
    public function __construct() { $this->baseUrl = config('services.product.url', 'http://product_service'); }
    public function getProductsByIds(string $tenantId, array $ids): array {
        if (empty($ids)) return [];
        try {
            $response = Http::timeout(5)->withHeaders(['X-Tenant-ID'=>$tenantId])->get("{$this->baseUrl}/api/products/lookup", ['ids'=>$ids]);
            return $response->successful() ? $response->json('data',[]) : [];
        } catch (\Throwable $e) { Log::error('ProductServiceClient: lookup failed',['error'=>$e->getMessage()]); return []; }
    }
}

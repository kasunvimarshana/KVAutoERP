<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProductServiceClient
{
    private string $baseUrl;
    private string $serviceToken;

    public function __construct()
    {
        $this->baseUrl      = rtrim(config('services.product_service_url', ''), '/');
        $this->serviceToken = config('services.service_token', '');
    }

    public function getProduct(string $productId, string $tenantId): ?array
    {
        if (empty($this->baseUrl)) {
            return null;
        }

        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'X-Service-Token' => $this->serviceToken,
                    'X-Tenant-ID'     => $tenantId,
                    'Accept'          => 'application/json',
                ])
                ->get("{$this->baseUrl}/internal/products", ['ids' => $productId]);

            if ($response->successful()) {
                $data = $response->json('data');
                if (is_array($data) && !empty($data)) {
                    return $data[0] ?? null;
                }
            }

            Log::warning('ProductServiceClient: getProduct failed', [
                'product_id' => $productId,
                'status'     => $response->status(),
            ]);

            return null;

        } catch (\Throwable $e) {
            Log::error('ProductServiceClient: getProduct exception', [
                'product_id' => $productId,
                'error'      => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function getProducts(array $productIds, string $tenantId): array
    {
        if (empty($this->baseUrl) || empty($productIds)) {
            return [];
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'X-Service-Token' => $this->serviceToken,
                    'X-Tenant-ID'     => $tenantId,
                    'Accept'          => 'application/json',
                ])
                ->get("{$this->baseUrl}/internal/products", [
                    'ids'      => implode(',', $productIds),
                    'per_page' => count($productIds),
                ]);

            if ($response->successful()) {
                return $response->json('data') ?? [];
            }

            Log::warning('ProductServiceClient: getProducts failed', [
                'status' => $response->status(),
            ]);

            return [];

        } catch (\Throwable $e) {
            Log::error('ProductServiceClient: getProducts exception', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    public function searchByName(string $name, string $tenantId): array
    {
        if (empty($this->baseUrl)) {
            return [];
        }

        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'X-Service-Token' => $this->serviceToken,
                    'X-Tenant-ID'     => $tenantId,
                    'Accept'          => 'application/json',
                ])
                ->get("{$this->baseUrl}/internal/products", ['search' => $name, 'per_page' => 100]);

            if ($response->successful()) {
                return $response->json('data') ?? [];
            }

            return [];

        } catch (\Throwable $e) {
            Log::error('ProductServiceClient: searchByName exception', ['error' => $e->getMessage()]);
            return [];
        }
    }
}

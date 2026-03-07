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

    public function validateProducts(array $productIds, string $tenantId): array
    {
        if (empty($this->baseUrl) || empty($productIds)) {
            return [];
        }

        try {
            $response = Http::timeout(5)
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

            Log::warning('ProductServiceClient: validateProducts failed', [
                'status' => $response->status(),
            ]);

            return [];

        } catch (\Throwable $e) {
            Log::error('ProductServiceClient: validateProducts exception', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    public function getProductPrices(array $productIds, string $tenantId): array
    {
        $products = $this->validateProducts($productIds, $tenantId);

        $prices = [];
        foreach ($products as $product) {
            $prices[$product['id']] = [
                'price' => $product['price'] ?? '0.00',
                'name'  => $product['name']  ?? '',
                'sku'   => $product['sku']   ?? '',
            ];
        }

        return $prices;
    }
}

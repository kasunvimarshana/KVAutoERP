<?php
namespace App\Infrastructure;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * HTTP client for the Product Service.
 *
 * Enables cross-service filtering of inventory by product attributes
 * (name, code, category) without direct database access — respects
 * microservice boundaries.
 */
class ProductServiceClient
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.product.url', 'http://product_service');
    }

    /**
     * Resolve product IDs matching given attribute filters.
     * Used for cross-service inventory filtering.
     *
     * @param string $tenantId
     * @param array  $filters  Supported: name (search), code, category_id
     * @return array  List of matching product UUIDs
     */
    public function resolveProductIds(string $tenantId, array $filters): array
    {
        $cacheKey = 'product_ids_' . hash('sha256', $tenantId . json_encode($filters));

        return Cache::remember($cacheKey, 30, function () use ($tenantId, $filters) {
            try {
                $query = [];
                if (!empty($filters['product_name']))  $query['search']      = $filters['product_name'];
                if (!empty($filters['product_code']))  $query['codes']       = [$filters['product_code']];
                if (!empty($filters['category_id']))   $query['category_id'] = $filters['category_id'];

                $response = Http::timeout(5)
                    ->withHeaders(['X-Tenant-ID' => $tenantId])
                    ->get("{$this->baseUrl}/api/products", $query);

                if (!$response->successful()) {
                    Log::warning('ProductServiceClient: product list failed', ['status' => $response->status()]);
                    return [];
                }

                return array_column($response->json('data', []), 'id');
            } catch (\Throwable $e) {
                Log::error('ProductServiceClient: request failed', ['error' => $e->getMessage()]);
                return [];
            }
        });
    }

    /**
     * Fetch full product details by IDs.
     * Uses the cross-service lookup endpoint.
     */
    public function getProductsByIds(string $tenantId, array $ids): array
    {
        if (empty($ids)) return [];

        try {
            $response = Http::timeout(5)
                ->withHeaders(['X-Tenant-ID' => $tenantId])
                ->get("{$this->baseUrl}/api/products/lookup", ['ids' => $ids]);

            return $response->successful() ? $response->json('data', []) : [];
        } catch (\Throwable $e) {
            Log::error('ProductServiceClient: lookup failed', ['error' => $e->getMessage()]);
            return [];
        }
    }
}

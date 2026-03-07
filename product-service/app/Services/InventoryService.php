<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.inventory.url', 'http://inventory-service:3000');
    }

    public function getInventoryByProductName(string $productName): array
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/api/inventory", [
                'product_name' => $productName,
            ]);

            return $response->successful() ? $response->json('data', []) : [];
        } catch (\Exception $e) {
            Log::warning('Inventory service unavailable: ' . $e->getMessage());

            return [];
        }
    }

    public function deleteByProductName(string $productName): bool
    {
        try {
            $response = Http::timeout(5)->delete("{$this->baseUrl}/api/inventory/product/{$productName}");

            return $response->successful();
        } catch (\Exception $e) {
            Log::warning('Inventory service unavailable: ' . $e->getMessage());

            return false;
        }
    }
}

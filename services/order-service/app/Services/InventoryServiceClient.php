<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InventoryServiceClient
{
    private string $baseUrl;
    private string $serviceToken;

    public function __construct()
    {
        $this->baseUrl      = rtrim(config('services.inventory_service_url', ''), '/');
        $this->serviceToken = config('services.service_token', '');
    }

    public function reserveStock(string $productId, int $quantity, string $tenantId, string $orderId): bool
    {
        if (empty($this->baseUrl)) {
            Log::warning('InventoryServiceClient: no base URL configured');
            return false;
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'X-Service-Token' => $this->serviceToken,
                    'X-Tenant-ID'     => $tenantId,
                    'Accept'          => 'application/json',
                ])
                ->post("{$this->baseUrl}/internal/inventory/reserve", [
                    'product_id'     => $productId,
                    'quantity'       => $quantity,
                    'reference_id'   => $orderId,
                    'reference_type' => 'order',
                ]);

            if ($response->successful()) {
                return true;
            }

            Log::warning('InventoryServiceClient: reserveStock failed', [
                'product_id' => $productId,
                'status'     => $response->status(),
                'body'       => $response->body(),
            ]);

            return false;

        } catch (\Throwable $e) {
            Log::error('InventoryServiceClient: reserveStock exception', [
                'product_id' => $productId,
                'error'      => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function releaseStock(string $productId, int $quantity, string $tenantId, string $orderId): bool
    {
        if (empty($this->baseUrl)) {
            return false;
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'X-Service-Token' => $this->serviceToken,
                    'X-Tenant-ID'     => $tenantId,
                    'Accept'          => 'application/json',
                ])
                ->post("{$this->baseUrl}/internal/inventory/release", [
                    'product_id'     => $productId,
                    'quantity'       => $quantity,
                    'reference_id'   => $orderId,
                    'reference_type' => 'order',
                ]);

            if ($response->successful()) {
                return true;
            }

            Log::warning('InventoryServiceClient: releaseStock failed', [
                'product_id' => $productId,
                'status'     => $response->status(),
            ]);

            return false;

        } catch (\Throwable $e) {
            Log::error('InventoryServiceClient: releaseStock exception', [
                'product_id' => $productId,
                'error'      => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function confirmStock(string $productId, int $quantity, string $tenantId, string $orderId): bool
    {
        if (empty($this->baseUrl)) {
            return false;
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'X-Service-Token' => $this->serviceToken,
                    'X-Tenant-ID'     => $tenantId,
                    'Accept'          => 'application/json',
                ])
                ->post("{$this->baseUrl}/internal/inventory/confirm", [
                    'product_id'     => $productId,
                    'quantity'       => $quantity,
                    'reference_id'   => $orderId,
                    'reference_type' => 'order',
                ]);

            if ($response->successful()) {
                return true;
            }

            Log::warning('InventoryServiceClient: confirmStock failed', [
                'product_id' => $productId,
                'status'     => $response->status(),
            ]);

            return false;

        } catch (\Throwable $e) {
            Log::error('InventoryServiceClient: confirmStock exception', [
                'product_id' => $productId,
                'error'      => $e->getMessage(),
            ]);

            return false;
        }
    }
}

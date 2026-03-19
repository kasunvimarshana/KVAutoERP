<?php

namespace App\Services\Pricing;

use App\Models\Product;
use App\Models\ProductPrice;
use Shared\Core\Pricing\PricingEngine;
use Shared\Core\Services\BaseService;
use Shared\Core\Events\AuditTrail;
use Shared\Core\Outbox\OutboxPublisher;
use Shared\Core\MultiTenancy\TenantManager;

class ProductPricingService extends BaseService
{
    protected $pricingEngine;

    public function __construct(
        PricingEngine $pricingEngine,
        AuditTrail $auditTrail,
        OutboxPublisher $outbox,
        TenantManager $tenantManager
    ) {
        parent::__construct($auditTrail, $outbox, $tenantManager);
        $this->pricingEngine = $pricingEngine;
    }

    /**
     * Calculates the final price for a product based on context (location, quantity, currency).
     */
    public function getFinalPrice(Product $product, array $context): float
    {
        $basePrice = $this->getBasePrice($product, $context['location_id'], $context['currency_code']);
        $rules = $this->getPriceRules($product, $context);

        return $this->pricingEngine->calculate($basePrice, $rules, $context);
    }

    protected function getBasePrice(Product $product, int $locationId, string $currencyCode): float
    {
        $priceRecord = ProductPrice::where('product_id', $product->id)
            ->where('location_id', $locationId)
            ->where('currency_code', $currencyCode)
            ->first();

        return $priceRecord ? (float) $priceRecord->price : 0.0;
    }

    protected function getPriceRules(Product $product, array $context): array
    {
        // Fetch rules from metadata or product-specific rules
        // In a real system, these would be SpEL expressions stored in PostgreSQL JSONB
        return [
            ['type' => 'percentage', 'value' => 10, 'expression' => '$quantity > 100'],
            ['type' => 'tiered', 'tiers' => [
                ['min' => 0, 'max' => 10, 'discount' => 0],
                ['min' => 11, 'max' => 50, 'discount' => 5],
                ['min' => 51, 'max' => null, 'discount' => 10],
            ], 'expression' => 'true', 'context_value' => $context['quantity'] ?? 0]
        ];
    }
}

<?php

namespace App\Services;

use App\Models\StockLevel;
use App\Models\Lot;
use Shared\Core\MultiTenancy\TenantManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class PharmaceuticalComplianceManager
{
    /**
     * @var TenantManager
     */
    protected $tenantManager;

    public function __construct(TenantManager $tenantManager)
    {
        $this->tenantManager = $tenantManager;
    }

    /**
     * Check if pharma compliance is enabled for the current tenant
     */
    public function isEnabled(): bool
    {
        $flags = config('features.flags');
        return isset($flags['pharma_compliance']) && $flags['pharma_compliance'] === true;
    }

    /**
     * Enforces compliance checks before any stock entry (IN)
     * 
     * @throws \Exception
     */
    public function validateStockIn(array $data): void
    {
        if (!$this->isEnabled()) return;

        // 1. Mandatory Lot Tracking
        if (empty($data['lot_id'])) {
            throw new \Exception("Lot tracking is mandatory under Pharmaceutical Compliance mode.");
        }

        $lot = Lot::find($data['lot_id']);
        if (!$lot) {
            throw new \Exception("Invalid Lot ID.");
        }

        // 2. Mandatory Expiry Date
        if (!$lot->expiry_date) {
            throw new \Exception("Expiry date is mandatory for all pharmaceutical lots.");
        }

        // 3. Expiry Check
        if ($lot->isExpired()) {
            throw new \Exception("Cannot stock expired pharmaceutical products. Lot: {$lot->lot_number}");
        }

        // 4. Quarantine Check
        if ($lot->is_quarantined) {
            throw new \Exception("Cannot stock items from a quarantined lot: {$lot->lot_number}");
        }

        Log::info("Pharmaceutical Compliance: IN validation passed for Lot: {$lot->lot_number}");
    }

    /**
     * Enforces compliance checks before any stock exit (OUT)
     * 
     * @throws \Exception
     */
    public function validateStockOut(array $data): void
    {
        if (!$this->isEnabled()) return;

        $lot = Lot::find($data['lot_id'] ?? null);
        if ($lot) {
            // 1. No Outgoing from Expired Lot
            if ($lot->isExpired()) {
                throw new \Exception("Cannot issue expired pharmaceutical products. Lot: {$lot->lot_number}");
            }

            // 2. No Outgoing from Quarantined Lot
            if ($lot->is_quarantined) {
                throw new \Exception("Cannot issue items from a quarantined lot: {$lot->lot_number}");
            }
        }

        Log::info("Pharmaceutical Compliance: OUT validation passed.");
    }

    /**
     * Enforce FEFO (First-Expired, First-Out)
     * 
     * @param int $productId
     * @param float $quantity
     * @return Collection
     */
    public function getAvailableStockFEFO(int $productId, float $quantity): Collection
    {
        $query = StockLevel::where('product_id', $productId)
            ->where('available_quantity', '>', 0)
            ->where('status', 'Available')
            ->join('lots', 'stock_levels.lot_id', '=', 'lots.id')
            ->where('lots.is_quarantined', false)
            ->where('lots.expiry_date', '>', now())
            ->orderBy('lots.expiry_date', 'asc') // FEFO: Earliest expiry first
            ->select('stock_levels.*');

        return $query->get();
    }

    /**
     * Audit log for compliance-related events
     */
    public function logComplianceEvent(string $type, string $message, array $metadata = []): void
    {
        Log::channel('compliance')->info("[{$type}] {$message}", $metadata);
    }
}

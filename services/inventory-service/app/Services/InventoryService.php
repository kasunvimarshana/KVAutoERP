<?php

namespace App\Services;

use App\Models\StockLedger;
use App\Models\StockLevel;
use App\Models\StockReservation;
use App\Models\Lot;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Shared\Core\Services\BaseService;

class InventoryService
{
    /**
     * @var PharmaceuticalComplianceManager
     */
    protected $complianceManager;

    public function __construct(PharmaceuticalComplianceManager $complianceManager)
    {
        $this->complianceManager = $complianceManager;
    }

    /**
     * Records a stock movement and updates current levels
     * 
     * @param array $data {
     *   product_id: int, warehouse_id: int, bin_location_id: int, lot_id: int, serial_id: int,
     *   quantity: float (positive for IN, negative for OUT), uom_id: int, transaction_type: string,
     *   reference_type: string, reference_id: int, reference_number: string, cost_at_transaction: float
     * }
     * @return StockLedger
     */
    public function recordTransaction(array $data): StockLedger
    {
        // Pharmaceutical Compliance Validation
        if ($data['quantity'] > 0) {
            $this->complianceManager->validateStockIn($data);
        } else {
            $this->complianceManager->validateStockOut($data);
        }

        return DB::transaction(function () use ($data) {
            // 1. Create Immutable Ledger Entry
            $ledger = StockLedger::create($data);

            // 2. Update Current Physical Level
            $level = StockLevel::firstOrCreate([
                'product_id' => $data['product_id'],
                'warehouse_id' => $data['warehouse_id'],
                'bin_location_id' => $data['bin_location_id'] ?? null,
                'lot_id' => $data['lot_id'] ?? null,
                'serial_id' => $data['serial_id'] ?? null,
                'uom_id' => $data['uom_id'],
            ], [
                'quantity' => 0,
                'reserved_quantity' => 0,
                'available_quantity' => 0,
                'status' => 'Available',
            ]);

            $level->quantity += $data['quantity'];
            $level->available_quantity = $level->quantity - $level->reserved_quantity;
            $level->last_ledger_id = $ledger->id;
            $level->save();

            Log::info("Stock movement recorded: {$data['transaction_type']} - Product: {$data['product_id']}, Qty: {$data['quantity']}");

            return $ledger;
        });
    }

    /**
     * Reserves stock for a given period
     * 
     * @param array $data {
     *   product_id: int, warehouse_id: int, quantity: float, reference_type: string,
     *   reference_id: int, reference_number: string, lot_id: int (optional), expiry_hours: int
     * }
     * @return StockReservation
     */
    public function reserveStock(array $data): StockReservation
    {
        return DB::transaction(function () use ($data) {
            // Find appropriate stock to reserve
            // In a real system, you would apply logic here for bin selection, FEFO/FIFO, etc.
            $query = StockLevel::where('product_id', $data['product_id'])
                ->where('warehouse_id', $data['warehouse_id'])
                ->where('available_quantity', '>=', $data['quantity'])
                ->where('status', 'Available');

            if (isset($data['lot_id'])) {
                $query->where('lot_id', $data['lot_id']);
            }

            // Simple implementation: pick the first available level
            $level = $query->first();

            if (!$level) {
                throw new \Exception("Insufficient stock available for reservation.");
            }

            // Create Reservation
            $reservation = StockReservation::create([
                'product_id' => $data['product_id'],
                'warehouse_id' => $data['warehouse_id'],
                'bin_location_id' => $level->bin_location_id,
                'lot_id' => $level->lot_id,
                'serial_id' => $level->serial_id,
                'quantity' => $data['quantity'],
                'uom_id' => $level->uom_id,
                'reference_type' => $data['reference_type'],
                'reference_id' => $data['reference_id'],
                'reference_number' => $data['reference_number'],
                'expiry_date' => now()->addHours($data['expiry_hours'] ?? 24),
                'status' => 'Active',
            ]);

            // Update Level Reserved Count
            $level->reserved_quantity += $data['quantity'];
            $level->available_quantity = $level->quantity - $level->reserved_quantity;
            $level->save();

            return $reservation;
        });
    }

    /**
     * Releases an active reservation back to available stock
     */
    public function releaseReservation(int $reservationId): bool
    {
        return DB::transaction(function () use ($reservationId) {
            $reservation = StockReservation::findOrFail($reservationId);

            if ($reservation->status !== 'Active') {
                return false;
            }

            $level = StockLevel::where([
                'product_id' => $reservation->product_id,
                'warehouse_id' => $reservation->warehouse_id,
                'bin_location_id' => $reservation->bin_location_id,
                'lot_id' => $reservation->lot_id,
                'serial_id' => $reservation->serial_id,
            ])->first();

            if ($level) {
                $level->reserved_quantity -= $reservation->quantity;
                $level->available_quantity = $level->quantity - $level->reserved_quantity;
                $level->save();
            }

            $reservation->status = 'Cancelled';
            return $reservation->save();
        });
    }

    /**
     * Deducts stock based on an active reservation (Fulfillment)
     */
    public function fulfillReservation(int $reservationId, string $transactionType): StockLedger
    {
        return DB::transaction(function () use ($reservationId, $transactionType) {
            $reservation = StockReservation::findOrFail($reservationId);

            if ($reservation->status !== 'Active') {
                throw new \Exception("Only active reservations can be fulfilled.");
            }

            // 1. Record the OUT movement
            $ledger = $this->recordTransaction([
                'product_id' => $reservation->product_id,
                'warehouse_id' => $reservation->warehouse_id,
                'bin_location_id' => $reservation->bin_location_id,
                'lot_id' => $reservation->lot_id,
                'serial_id' => $reservation->serial_id,
                'quantity' => -$reservation->quantity, // Negative for OUT
                'uom_id' => $reservation->uom_id,
                'transaction_type' => $transactionType,
                'reference_type' => $reservation->reference_type,
                'reference_id' => $reservation->reference_id,
                'reference_number' => $reservation->reference_number,
                'cost_at_transaction' => 0, // In a real system, calculate based on FIFO/LIFO
            ]);

            // 2. Reduce the reserved count (since it's now deducted from physical stock)
            $level = StockLevel::where([
                'product_id' => $reservation->product_id,
                'warehouse_id' => $reservation->warehouse_id,
                'bin_location_id' => $reservation->bin_location_id,
                'lot_id' => $reservation->lot_id,
                'serial_id' => $reservation->serial_id,
            ])->first();

            if ($level) {
                $level->reserved_quantity -= $reservation->quantity;
                $level->available_quantity = $level->quantity - $level->reserved_quantity;
                $level->save();
            }

            $reservation->status = 'Fulfilled';
            $reservation->save();

            return $ledger;
        });
    }
}

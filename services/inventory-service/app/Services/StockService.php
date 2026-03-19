<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\StockItemRepositoryInterface;
use App\Contracts\Repositories\StockLedgerRepositoryInterface;
use App\Contracts\Services\StockServiceInterface;
use App\Models\CycleCount;
use App\Models\CycleCountLine;
use App\Models\ReorderRule;
use App\Models\StockItem;
use App\Models\StockLedger;
use App\Models\StockReservation;
use App\Models\StockTransfer;
use App\Models\StockTransferLine;
use App\Models\Warehouse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use KvEnterprise\SharedKernel\DTOs\FilterDTO;
use KvEnterprise\SharedKernel\Exceptions\DomainException;
use KvEnterprise\SharedKernel\Exceptions\NotFoundException;
use KvEnterprise\SharedKernel\Exceptions\ValidationException;

/**
 * Core inventory / stock application service.
 *
 * All stock mutations go through this service. Every mutation:
 *   1. Validates the request.
 *   2. Acquires pessimistic locks where needed.
 *   3. Applies the change to the StockItem aggregate.
 *   4. Appends an immutable StockLedger entry.
 *   5. All within a single database transaction.
 *
 * Idempotency is enforced via the idempotency_key on the ledger table.
 */
final class StockService implements StockServiceInterface
{
    public function __construct(
        private readonly StockItemRepositoryInterface  $stockItemRepository,
        private readonly StockLedgerRepositoryInterface $stockLedgerRepository,
    ) {}

    // -------------------------------------------------------------------------
    // Stock Levels
    // -------------------------------------------------------------------------

    /**
     * Return paginated stock items.
     *
     * @param  array<string, mixed>  $filters
     * @param  int                   $page
     * @param  int                   $perPage
     * @return LengthAwarePaginator<StockItem>
     */
    public function listStock(array $filters = [], int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        $filterDTO = new FilterDTO(
            filters: array_filter([
                'product_id'   => $filters['product_id'] ?? null,
                'warehouse_id' => $filters['warehouse_id'] ?? null,
            ], static fn ($v) => $v !== null && $v !== ''),
            sorts:  [],
            search: null,
        );

        return $this->stockItemRepository->paginate($page, $perPage, $filterDTO);
    }

    /**
     * Return all stock items for a product.
     *
     * @param  string  $productId
     * @return array<int, StockItem>
     */
    public function getProductStock(string $productId): array
    {
        return $this->stockItemRepository->findByProduct($productId);
    }

    // -------------------------------------------------------------------------
    // Stock Movements
    // -------------------------------------------------------------------------

    /**
     * Receive stock into a warehouse.
     *
     * @param  array<string, mixed>  $data
     * @return StockLedger
     */
    public function receive(array $data): StockLedger
    {
        return $this->applyMovement('receive', $data, true);
    }

    /**
     * Dispatch stock from a warehouse.
     *
     * @param  array<string, mixed>  $data
     * @return StockLedger
     */
    public function dispatch(array $data): StockLedger
    {
        return $this->applyMovement('dispatch', $data, false);
    }

    /**
     * Record a stock adjustment.
     *
     * @param  array<string, mixed>  $data
     * @return StockLedger
     */
    public function adjust(array $data): StockLedger
    {
        $qty = bcadd((string) ($data['qty'] ?? '0'), '0', 4);
        $isInflow = bccomp($qty, '0', 4) > 0;

        // Pass the absolute qty value; direction is conveyed via isInflow.
        $data['qty'] = bccomp($qty, '0', 4) < 0 ? ltrim($qty, '-') : $qty;

        return $this->applyMovement('adjustment', $data, $isInflow);
    }

    /**
     * Transfer stock between warehouses/bins.
     *
     * Creates a StockTransfer with lines, then posts transfer_out from
     * the source and transfer_in to the destination, all atomically.
     *
     * @param  array<string, mixed>  $data
     * @return StockTransfer
     */
    public function transfer(array $data): StockTransfer
    {
        return DB::transaction(function () use ($data): StockTransfer {
            $fromWarehouseId = $data['from_warehouse_id'];
            $toWarehouseId   = $data['to_warehouse_id'];
            $fromBinId       = $data['from_bin_id'] ?? null;
            $toBinId         = $data['to_bin_id'] ?? null;
            $lines           = $data['lines'] ?? [];

            // Validate warehouses exist.
            $fromWarehouse = Warehouse::find($fromWarehouseId);
            $toWarehouse   = Warehouse::find($toWarehouseId);

            if ($fromWarehouse === null) {
                throw NotFoundException::for('Warehouse', $fromWarehouseId);
            }
            if ($toWarehouse === null) {
                throw NotFoundException::for('Warehouse', $toWarehouseId);
            }

            if ($fromWarehouseId === $toWarehouseId && $fromBinId === $toBinId) {
                throw new DomainException('Source and destination cannot be the same location.');
            }

            // Build transfer header.
            $transfer = StockTransfer::create([
                'organization_id'   => $data['organization_id'] ?? null,
                'transfer_number'   => $data['transfer_number'] ?? null,
                'status'            => 'in_transit',
                'transfer_type'     => $data['transfer_type'] ?? 'internal',
                'from_warehouse_id' => $fromWarehouseId,
                'from_bin_id'       => $fromBinId,
                'to_warehouse_id'   => $toWarehouseId,
                'to_bin_id'         => $toBinId,
                'notes'             => $data['notes'] ?? null,
                'requested_at'      => now(),
                'dispatched_at'     => now(),
                'received_at'       => now(),
                'created_by'        => $data['performed_by'] ?? null,
            ]);

            foreach ($lines as $line) {
                $productId = $line['product_id'];
                $qty       = bcadd((string) $line['qty'], '0', 4);
                $lotId     = $line['lot_id'] ?? null;
                $unitCost  = bcadd((string) ($line['unit_cost'] ?? '0'), '0', 4);

                // Create transfer line.
                StockTransferLine::create([
                    'transfer_id'    => $transfer->id,
                    'tenant_id'      => $transfer->tenant_id,
                    'product_id'     => $productId,
                    'lot_id'         => $lotId,
                    'qty_requested'  => $qty,
                    'qty_dispatched' => $qty,
                    'qty_received'   => $qty,
                    'unit_cost'      => $unitCost,
                ]);

                // Transfer out from source (with pessimistic lock).
                $fromItem = $this->stockItemRepository->findByLocation(
                    $productId, $fromWarehouseId, $fromBinId, $lotId, true,
                );

                if ($fromItem === null || bccomp($fromItem->qty_available, $qty, 4) < 0) {
                    throw new DomainException(
                        "Insufficient stock for product {$productId} at source warehouse.",
                    );
                }

                $fromItem = $this->stockItemRepository->decrementOnHand($fromItem, $qty);

                $fromAfterQty = $fromItem->qty_on_hand;

                $this->stockLedgerRepository->append([
                    'product_id'       => $productId,
                    'warehouse_id'     => $fromWarehouseId,
                    'bin_id'           => $fromBinId,
                    'lot_id'           => $lotId,
                    'transaction_type' => 'transfer_out',
                    'reference_type'   => 'stock_transfer',
                    'reference_id'     => $transfer->id,
                    'qty_change'       => '-' . $qty,
                    'qty_after'        => $fromAfterQty,
                    'unit_cost'        => $unitCost,
                    'total_cost'       => bcmul($qty, $unitCost, 4),
                    'performed_by'     => $data['performed_by'] ?? null,
                    'transacted_at'    => now(),
                ]);

                // Transfer in to destination.
                $toItem = $this->stockItemRepository->findByLocation(
                    $productId, $toWarehouseId, $toBinId, $lotId,
                );

                if ($toItem === null) {
                    $toItem = $this->stockItemRepository->upsert(
                        $productId, $toWarehouseId, $toBinId, $lotId,
                        ['qty_on_hand' => '0.0000', 'qty_reserved' => '0.0000', 'unit_cost' => $unitCost],
                    );
                }

                $toItem = $this->stockItemRepository->incrementOnHand($toItem, $qty);
                $toAfterQty = $toItem->qty_on_hand;

                $this->stockLedgerRepository->append([
                    'product_id'       => $productId,
                    'warehouse_id'     => $toWarehouseId,
                    'bin_id'           => $toBinId,
                    'lot_id'           => $lotId,
                    'transaction_type' => 'transfer_in',
                    'reference_type'   => 'stock_transfer',
                    'reference_id'     => $transfer->id,
                    'qty_change'       => $qty,
                    'qty_after'        => $toAfterQty,
                    'unit_cost'        => $unitCost,
                    'total_cost'       => bcmul($qty, $unitCost, 4),
                    'performed_by'     => $data['performed_by'] ?? null,
                    'transacted_at'    => now(),
                ]);
            }

            $transfer->update(['status' => 'completed']);

            return $transfer->load('lines');
        });
    }

    // -------------------------------------------------------------------------
    // Reservations
    // -------------------------------------------------------------------------

    /**
     * Reserve stock for an order.
     *
     * @param  array<string, mixed>  $data
     * @return StockReservation
     */
    public function reserve(array $data): StockReservation
    {
        return DB::transaction(function () use ($data): StockReservation {
            $productId   = $data['product_id'];
            $warehouseId = $data['warehouse_id'];
            $binId       = $data['bin_id'] ?? null;
            $lotId       = $data['lot_id'] ?? null;
            $qty         = bcadd((string) $data['qty'], '0', 4);

            // Pessimistic lock during reservation.
            $item = $this->stockItemRepository->findByLocation($productId, $warehouseId, $binId, $lotId, true);

            if ($item === null || bccomp($item->qty_available, $qty, 4) < 0) {
                throw new DomainException(
                    "Insufficient available stock to reserve {$qty} units of product {$productId}.",
                );
            }

            $reservation = StockReservation::create([
                'product_id'     => $productId,
                'warehouse_id'   => $warehouseId,
                'bin_id'         => $binId,
                'lot_id'         => $lotId,
                'reference_type' => $data['reference_type'],
                'reference_id'   => $data['reference_id'],
                'qty_reserved'   => $qty,
                'qty_fulfilled'  => '0.0000',
                'qty_remaining'  => $qty,
                'status'         => 'active',
                'expires_at'     => $data['expires_at'] ?? null,
                'notes'          => $data['notes'] ?? null,
                'created_by'     => $data['performed_by'] ?? null,
            ]);

            $this->stockItemRepository->incrementReserved($item, $qty);

            $this->stockLedgerRepository->append([
                'product_id'       => $productId,
                'warehouse_id'     => $warehouseId,
                'bin_id'           => $binId,
                'lot_id'           => $lotId,
                'transaction_type' => 'reservation',
                'reference_type'   => $data['reference_type'],
                'reference_id'     => $data['reference_id'],
                'qty_change'       => '-' . $qty,
                'qty_after'        => $item->qty_on_hand,
                'unit_cost'        => $item->unit_cost,
                'total_cost'       => bcmul($qty, $item->unit_cost, 4),
                'performed_by'     => $data['performed_by'] ?? null,
                'transacted_at'    => now(),
            ]);

            return $reservation;
        });
    }

    /**
     * Release a reservation and restore available stock.
     *
     * @param  string  $reservationId
     * @return void
     */
    public function releaseReservation(string $reservationId): void
    {
        DB::transaction(function () use ($reservationId): void {
            $reservation = StockReservation::find($reservationId);

            if ($reservation === null) {
                throw NotFoundException::for('StockReservation', $reservationId);
            }

            if (!$reservation->isActive()) {
                throw new DomainException('Reservation is not in an active state.');
            }

            $releaseQty = $reservation->qty_remaining;

            $item = $this->stockItemRepository->findByLocation(
                $reservation->product_id,
                $reservation->warehouse_id,
                $reservation->bin_id,
                $reservation->lot_id,
                true,
            );

            if ($item !== null) {
                $this->stockItemRepository->decrementReserved($item, $releaseQty);

                $this->stockLedgerRepository->append([
                    'product_id'       => $reservation->product_id,
                    'warehouse_id'     => $reservation->warehouse_id,
                    'bin_id'           => $reservation->bin_id,
                    'lot_id'           => $reservation->lot_id,
                    'transaction_type' => 'reservation_release',
                    'reference_type'   => $reservation->reference_type,
                    'reference_id'     => $reservation->reference_id,
                    'qty_change'       => $releaseQty,
                    'qty_after'        => $item->qty_on_hand,
                    'unit_cost'        => $item->unit_cost,
                    'total_cost'       => bcmul($releaseQty, $item->unit_cost, 4),
                    'transacted_at'    => now(),
                ]);
            }

            $reservation->update(['status' => 'cancelled']);
        });
    }

    // -------------------------------------------------------------------------
    // Ledger
    // -------------------------------------------------------------------------

    /**
     * Return paginated ledger entries.
     *
     * @param  array<string, mixed>  $filters
     * @param  int                   $page
     * @param  int                   $perPage
     * @return LengthAwarePaginator<StockLedger>
     */
    public function getLedger(array $filters = [], int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        $filterDTO = new FilterDTO(
            filters: array_filter([
                'product_id'       => $filters['product_id'] ?? null,
                'warehouse_id'     => $filters['warehouse_id'] ?? null,
                'transaction_type' => $filters['transaction_type'] ?? null,
                'reference_type'   => $filters['reference_type'] ?? null,
                'reference_id'     => $filters['reference_id'] ?? null,
                'from_date'        => $filters['from_date'] ?? null,
                'to_date'          => $filters['to_date'] ?? null,
            ], static fn ($v) => $v !== null && $v !== ''),
            sorts:  [],
            search: null,
        );

        return $this->stockLedgerRepository->paginate($page, $perPage, $filterDTO);
    }

    // -------------------------------------------------------------------------
    // Reservations (list)
    // -------------------------------------------------------------------------

    /**
     * Return paginated reservations.
     *
     * @param  array<string, mixed>  $filters
     * @param  int                   $page
     * @param  int                   $perPage
     * @return LengthAwarePaginator<StockReservation>
     */
    public function listReservations(array $filters = [], int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        $query = StockReservation::query();

        foreach (['product_id', 'warehouse_id', 'status', 'reference_type', 'reference_id'] as $column) {
            if (!empty($filters[$column])) {
                $query->where($column, $filters[$column]);
            }
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    // -------------------------------------------------------------------------
    // Reorder Rules
    // -------------------------------------------------------------------------

    /**
     * Create or update a reorder rule.
     *
     * @param  array<string, mixed>  $data
     * @return ReorderRule
     */
    public function upsertReorderRule(array $data): ReorderRule
    {
        $existing = ReorderRule::where('product_id', $data['product_id'])
            ->where('warehouse_id', $data['warehouse_id'])
            ->first();

        if ($existing !== null) {
            $existing->update($data);

            return $existing->fresh() ?? $existing;
        }

        return ReorderRule::create($data);
    }

    /**
     * Update an existing reorder rule.
     *
     * @param  string                $ruleId
     * @param  array<string, mixed>  $data
     * @return ReorderRule
     */
    public function updateReorderRule(string $ruleId, array $data): ReorderRule
    {
        $rule = ReorderRule::find($ruleId);

        if ($rule === null) {
            throw NotFoundException::for('ReorderRule', $ruleId);
        }

        $rule->update($data);

        return $rule->fresh() ?? $rule;
    }

    /**
     * Delete a reorder rule.
     *
     * @param  string  $ruleId
     * @return void
     */
    public function deleteReorderRule(string $ruleId): void
    {
        $rule = ReorderRule::find($ruleId);

        if ($rule === null) {
            throw NotFoundException::for('ReorderRule', $ruleId);
        }

        $rule->delete();
    }

    /**
     * Return paginated reorder rules.
     *
     * @param  array<string, mixed>  $filters
     * @param  int                   $page
     * @param  int                   $perPage
     * @return LengthAwarePaginator<ReorderRule>
     */
    public function listReorderRules(array $filters = [], int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        $query = ReorderRule::query();

        foreach (['product_id', 'warehouse_id', 'is_active'] as $column) {
            if (isset($filters[$column]) && $filters[$column] !== '') {
                $query->where($column, $filters[$column]);
            }
        }

        return $query->orderBy('product_id')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    // -------------------------------------------------------------------------
    // Cycle Counts
    // -------------------------------------------------------------------------

    /**
     * Open a new cycle count session.
     *
     * @param  array<string, mixed>  $data
     * @return CycleCount
     */
    public function openCycleCount(array $data): CycleCount
    {
        return DB::transaction(function () use ($data): CycleCount {
            $warehouseId = $data['warehouse_id'];

            if (Warehouse::find($warehouseId) === null) {
                throw NotFoundException::for('Warehouse', $warehouseId);
            }

            $cycleCount = CycleCount::create([
                'warehouse_id' => $warehouseId,
                'count_number' => $data['count_number'] ?? null,
                'count_type'   => $data['count_type'] ?? 'full',
                'status'       => 'in_progress',
                'scheduled_at' => $data['scheduled_at'] ?? null,
                'started_at'   => now(),
                'notes'        => $data['notes'] ?? null,
                'created_by'   => $data['performed_by'] ?? null,
            ]);

            // Create count lines from current stock snapshot.
            // If products are specified, create lines for those products only.
            $productIds = $data['product_ids'] ?? [];

            if (!empty($productIds)) {
                $stockItems = StockItem::where('warehouse_id', $warehouseId)
                    ->whereIn('product_id', $productIds)
                    ->get();
            } else {
                $stockItems = StockItem::where('warehouse_id', $warehouseId)->get();
            }

            foreach ($stockItems as $stockItem) {
                CycleCountLine::create([
                    'cycle_count_id' => $cycleCount->id,
                    'tenant_id'      => $cycleCount->tenant_id,
                    'product_id'     => $stockItem->product_id,
                    'bin_id'         => $stockItem->bin_id,
                    'lot_id'         => $stockItem->lot_id,
                    'system_qty'     => $stockItem->qty_on_hand,
                    'counted_qty'    => null,
                    'unit_cost'      => $stockItem->unit_cost,
                    'uom_id'         => $stockItem->uom_id,
                    'count_status'   => 'pending',
                ]);
            }

            return $cycleCount->load('lines');
        });
    }

    /**
     * Find a cycle count or throw NotFoundException.
     *
     * @param  string  $id
     * @return CycleCount
     */
    public function findCycleCountOrFail(string $id): CycleCount
    {
        $cycleCount = CycleCount::with('lines')->find($id);

        if ($cycleCount === null) {
            throw NotFoundException::for('CycleCount', $id);
        }

        return $cycleCount;
    }

    /**
     * Return paginated cycle counts.
     *
     * @param  array<string, mixed>  $filters
     * @param  int                   $page
     * @param  int                   $perPage
     * @return LengthAwarePaginator<CycleCount>
     */
    public function listCycleCounts(array $filters = [], int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        $query = CycleCount::query();

        foreach (['warehouse_id', 'status', 'count_type'] as $column) {
            if (!empty($filters[$column])) {
                $query->where($column, $filters[$column]);
            }
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Confirm a cycle count — post adjustment entries for all variances.
     *
     * @param  string                $id
     * @param  array<string, mixed>  $countedData  Array of {line_id: string, counted_qty: string}.
     * @return CycleCount
     */
    public function confirmCycleCount(string $id, array $countedData): CycleCount
    {
        return DB::transaction(function () use ($id, $countedData): CycleCount {
            $cycleCount = $this->findCycleCountOrFail($id);

            if (!$cycleCount->canConfirm()) {
                throw new DomainException("Cycle count cannot be confirmed in status: {$cycleCount->status}");
            }

            // Index counted data by line_id for quick lookup.
            $countedByLineId = [];
            foreach ($countedData as $entry) {
                $countedByLineId[(string) $entry['line_id']] = bcadd((string) $entry['counted_qty'], '0', 4);
            }

            foreach ($cycleCount->lines as $line) {
                $countedQty = $countedByLineId[$line->id] ?? null;

                if ($countedQty === null) {
                    continue;
                }

                $line->update([
                    'counted_qty'  => $countedQty,
                    'count_status' => 'counted',
                    'counted_at'   => now(),
                ]);

                $variance = bcsub($countedQty, $line->system_qty, 4);

                // Only post adjustment if there is a variance.
                if (bccomp($variance, '0', 4) === 0) {
                    continue;
                }

                $item = $this->stockItemRepository->findByLocation(
                    $line->product_id,
                    $cycleCount->warehouse_id,
                    $line->bin_id,
                    $line->lot_id,
                    true,
                );

                if ($item !== null) {
                    if (bccomp($variance, '0', 4) > 0) {
                        $item = $this->stockItemRepository->incrementOnHand($item, $variance);
                    } else {
                        $absVariance = ltrim($variance, '-');
                        $item = $this->stockItemRepository->decrementOnHand($item, $absVariance);
                    }

                    $this->stockLedgerRepository->append([
                        'product_id'       => $line->product_id,
                        'warehouse_id'     => $cycleCount->warehouse_id,
                        'bin_id'           => $line->bin_id,
                        'lot_id'           => $line->lot_id,
                        'transaction_type' => 'cycle_count',
                        'reference_type'   => 'cycle_count',
                        'reference_id'     => $cycleCount->id,
                        'qty_change'       => $variance,
                        'qty_after'        => $item->qty_on_hand,
                        'unit_cost'        => $line->unit_cost,
                        'total_cost'       => bcmul(ltrim($variance, '-'), $line->unit_cost, 4),
                        'notes'            => "Cycle count variance adjustment for count {$cycleCount->count_number}",
                        'transacted_at'    => now(),
                    ]);
                }

                $line->update(['count_status' => 'adjusted']);
            }

            $cycleCount->update([
                'status'       => 'confirmed',
                'completed_at' => now(),
                'confirmed_at' => now(),
            ]);

            return $cycleCount->load('lines');
        });
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Apply a stock movement (receive/dispatch/adjustment) atomically.
     *
     * @param  string                $type
     * @param  array<string, mixed>  $data
     * @param  bool                  $isInflow
     * @return StockLedger
     */
    private function applyMovement(string $type, array $data, bool $isInflow): StockLedger
    {
        return DB::transaction(function () use ($type, $data, $isInflow): StockLedger {
            $productId   = $data['product_id'];
            $warehouseId = $data['warehouse_id'];
            $binId       = $data['bin_id'] ?? null;
            $lotId       = $data['lot_id'] ?? null;
            $qty         = bcadd((string) ($data['qty'] ?? '0'), '0', 4);
            $unitCost    = bcadd((string) ($data['unit_cost'] ?? '0'), '0', 4);
            $currency    = $data['currency'] ?? 'USD';
            $performedBy = $data['performed_by'] ?? null;

            $idempotencyKey = $data['idempotency_key'] ?? null;

            // Idempotency check.
            if ($idempotencyKey !== null) {
                $existing = $this->stockLedgerRepository->findByIdempotencyKey($idempotencyKey);
                if ($existing !== null) {
                    return $existing;
                }
            }

            // For outflows, use pessimistic lock.
            $item = $this->stockItemRepository->findByLocation(
                $productId, $warehouseId, $binId, $lotId, !$isInflow,
            );

            if ($item === null) {
                if (!$isInflow) {
                    throw new DomainException(
                        "No stock found for product {$productId} at warehouse {$warehouseId}.",
                    );
                }

                $item = $this->stockItemRepository->upsert(
                    $productId, $warehouseId, $binId, $lotId,
                    ['qty_on_hand' => '0.0000', 'qty_reserved' => '0.0000', 'unit_cost' => $unitCost],
                );
            }

            if ($isInflow) {
                $item = $this->stockItemRepository->incrementOnHand($item, $qty);
                // Update weighted average cost on receive.
                if ($type === 'receive' && bccomp($unitCost, '0', 4) > 0) {
                    $this->updateWeightedAverageCost($item, $qty, $unitCost);
                    $item = $item->fresh() ?? $item;
                }
            } else {
                $item = $this->stockItemRepository->decrementOnHand($item, $qty);
            }

            $qtyChange = $isInflow ? $qty : ('-' . $qty);

            return $this->stockLedgerRepository->append([
                'product_id'       => $productId,
                'warehouse_id'     => $warehouseId,
                'bin_id'           => $binId,
                'lot_id'           => $lotId,
                'transaction_type' => $type,
                'reference_type'   => $data['reference_type'] ?? null,
                'reference_id'     => $data['reference_id'] ?? null,
                'idempotency_key'  => $idempotencyKey,
                'qty_change'       => $qtyChange,
                'qty_after'        => $item->qty_on_hand,
                'unit_cost'        => $unitCost,
                'total_cost'       => bcmul($qty, $unitCost, 4),
                'currency'         => $currency,
                'uom_id'           => $data['uom_id'] ?? null,
                'notes'            => $data['notes'] ?? null,
                'metadata'         => $data['metadata'] ?? null,
                'performed_by'     => $performedBy,
                'transacted_at'    => $data['transacted_at'] ?? now(),
            ]);
        });
    }

    /**
     * Update the weighted average unit cost of a stock item after a receipt.
     *
     * Formula: (existing_qty * old_cost + received_qty * new_cost) / (existing_qty + received_qty)
     *
     * @param  StockItem  $item
     * @param  string     $receivedQty
     * @param  string     $receivedCost
     * @return void
     */
    private function updateWeightedAverageCost(StockItem $item, string $receivedQty, string $receivedCost): void
    {
        $existingQty  = bcsub($item->qty_on_hand, $receivedQty, 4);
        $totalCostOld = bcmul($existingQty, $item->unit_cost, 4);
        $totalCostNew = bcmul($receivedQty, $receivedCost, 4);
        $totalCost    = bcadd($totalCostOld, $totalCostNew, 4);
        $totalQty     = $item->qty_on_hand;

        if (bccomp($totalQty, '0', 4) > 0) {
            $newCost = bcdiv($totalCost, $totalQty, 4);
            $item->update(['unit_cost' => $newCost]);
        }
    }
}

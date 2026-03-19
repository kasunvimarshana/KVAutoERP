<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Models\CycleCount;
use App\Models\ReorderRule;
use App\Models\StockItem;
use App\Models\StockLedger;
use App\Models\StockReservation;
use App\Models\StockTransfer;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Contract for the core inventory / stock application service.
 *
 * All mutating operations (receive, dispatch, adjust, transfer) are
 * transactional, ledger-driven, and idempotent when an idempotency_key
 * is provided.
 */
interface StockServiceInterface
{
    /**
     * Return paginated stock items (current on-hand levels).
     *
     * @param  array<string, mixed>  $filters
     * @param  int                   $page
     * @param  int                   $perPage
     * @return LengthAwarePaginator<StockItem>
     */
    public function listStock(array $filters = [], int $page = 1, int $perPage = 15): LengthAwarePaginator;

    /**
     * Return all stock items for a specific product across all warehouses.
     *
     * @param  string  $productId
     * @return array<int, StockItem>
     */
    public function getProductStock(string $productId): array;

    /**
     * Receive stock into a warehouse (inbound movement).
     *
     * Creates a StockLedger entry of type 'receive' and increments
     * the StockItem on-hand quantity.
     *
     * @param  array<string, mixed>  $data
     * @return StockLedger
     *
     * @throws \KvEnterprise\SharedKernel\Exceptions\ValidationException
     * @throws \KvEnterprise\SharedKernel\Exceptions\DomainException
     */
    public function receive(array $data): StockLedger;

    /**
     * Dispatch stock from a warehouse (outbound movement).
     *
     * Creates a StockLedger entry of type 'dispatch' and decrements
     * the StockItem on-hand quantity using pessimistic locking.
     *
     * @param  array<string, mixed>  $data
     * @return StockLedger
     *
     * @throws \KvEnterprise\SharedKernel\Exceptions\ValidationException
     * @throws \KvEnterprise\SharedKernel\Exceptions\DomainException  On insufficient stock.
     */
    public function dispatch(array $data): StockLedger;

    /**
     * Record a stock adjustment (positive or negative quantity correction).
     *
     * @param  array<string, mixed>  $data
     * @return StockLedger
     *
     * @throws \KvEnterprise\SharedKernel\Exceptions\ValidationException
     */
    public function adjust(array $data): StockLedger;

    /**
     * Initiate a stock transfer between warehouses/bins.
     *
     * @param  array<string, mixed>  $data
     * @return StockTransfer
     *
     * @throws \KvEnterprise\SharedKernel\Exceptions\ValidationException
     * @throws \KvEnterprise\SharedKernel\Exceptions\DomainException
     */
    public function transfer(array $data): StockTransfer;

    /**
     * Reserve stock for an order or process.
     *
     * @param  array<string, mixed>  $data
     * @return StockReservation
     *
     * @throws \KvEnterprise\SharedKernel\Exceptions\ValidationException
     * @throws \KvEnterprise\SharedKernel\Exceptions\DomainException  On insufficient available stock.
     */
    public function reserve(array $data): StockReservation;

    /**
     * Release a stock reservation.
     *
     * @param  string  $reservationId
     * @return void
     *
     * @throws \KvEnterprise\SharedKernel\Exceptions\NotFoundException
     */
    public function releaseReservation(string $reservationId): void;

    /**
     * Return paginated stock ledger entries.
     *
     * @param  array<string, mixed>  $filters
     * @param  int                   $page
     * @param  int                   $perPage
     * @return LengthAwarePaginator<StockLedger>
     */
    public function getLedger(array $filters = [], int $page = 1, int $perPage = 15): LengthAwarePaginator;

    /**
     * Return paginated reservations.
     *
     * @param  array<string, mixed>  $filters
     * @param  int                   $page
     * @param  int                   $perPage
     * @return LengthAwarePaginator<StockReservation>
     */
    public function listReservations(array $filters = [], int $page = 1, int $perPage = 15): LengthAwarePaginator;

    /**
     * Create or update a reorder rule.
     *
     * @param  array<string, mixed>  $data
     * @return ReorderRule
     */
    public function upsertReorderRule(array $data): ReorderRule;

    /**
     * Update an existing reorder rule.
     *
     * @param  string                $ruleId
     * @param  array<string, mixed>  $data
     * @return ReorderRule
     *
     * @throws \KvEnterprise\SharedKernel\Exceptions\NotFoundException
     */
    public function updateReorderRule(string $ruleId, array $data): ReorderRule;

    /**
     * Delete a reorder rule.
     *
     * @param  string  $ruleId
     * @return void
     *
     * @throws \KvEnterprise\SharedKernel\Exceptions\NotFoundException
     */
    public function deleteReorderRule(string $ruleId): void;

    /**
     * Return paginated reorder rules.
     *
     * @param  array<string, mixed>  $filters
     * @param  int                   $page
     * @param  int                   $perPage
     * @return LengthAwarePaginator<ReorderRule>
     */
    public function listReorderRules(array $filters = [], int $page = 1, int $perPage = 15): LengthAwarePaginator;

    /**
     * Open a new cycle count session.
     *
     * @param  array<string, mixed>  $data
     * @return CycleCount
     */
    public function openCycleCount(array $data): CycleCount;

    /**
     * Find a cycle count by UUID.
     *
     * @param  string  $id
     * @return CycleCount
     *
     * @throws \KvEnterprise\SharedKernel\Exceptions\NotFoundException
     */
    public function findCycleCountOrFail(string $id): CycleCount;

    /**
     * Return paginated cycle counts.
     *
     * @param  array<string, mixed>  $filters
     * @param  int                   $page
     * @param  int                   $perPage
     * @return LengthAwarePaginator<CycleCount>
     */
    public function listCycleCounts(array $filters = [], int $page = 1, int $perPage = 15): LengthAwarePaginator;

    /**
     * Confirm a cycle count — post adjustment ledger entries for variances.
     *
     * @param  string                $id
     * @param  array<string, mixed>  $countedData  Array of {line_id, counted_qty} maps.
     * @return CycleCount
     *
     * @throws \KvEnterprise\SharedKernel\Exceptions\NotFoundException
     * @throws \KvEnterprise\SharedKernel\Exceptions\DomainException
     */
    public function confirmCycleCount(string $id, array $countedData): CycleCount;
}

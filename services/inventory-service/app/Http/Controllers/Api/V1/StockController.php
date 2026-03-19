<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Contracts\Services\StockServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\StockAdjustmentRequest;
use App\Http\Requests\StockDispatchRequest;
use App\Http\Requests\StockReceiveRequest;
use App\Http\Requests\StockReservationRequest;
use App\Http\Requests\StockTransferRequest;
use App\Http\Resources\StockItemResource;
use App\Http\Resources\StockLedgerResource;
use App\Http\Resources\StockReservationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use KvEnterprise\SharedKernel\DTOs\PaginationDTO;
use KvEnterprise\SharedKernel\Exceptions\DomainException;
use KvEnterprise\SharedKernel\Exceptions\NotFoundException;
use KvEnterprise\SharedKernel\Exceptions\ValidationException;
use KvEnterprise\SharedKernel\Http\Responses\ApiResponse;

/**
 * Stock and inventory controller (v1).
 *
 * Handles stock levels, movements (receive/dispatch/adjust/transfer),
 * reservations, ledger queries, reorder rules, and cycle counts.
 * Thin controller — all business logic lives in StockService.
 */
final class StockController extends Controller
{
    public function __construct(
        private readonly StockServiceInterface $stockService,
    ) {}

    // -------------------------------------------------------------------------
    // Stock Levels
    // -------------------------------------------------------------------------

    /**
     * List current stock levels.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $this->resolvePerPage($request);
        $page    = max(1, (int) $request->query('page', 1));
        $filters = $request->only(['product_id', 'warehouse_id']);

        $paginator  = $this->stockService->listStock($filters, $page, $perPage);
        $pagination = $this->buildPagination($paginator);

        return ApiResponse::paginated(
            StockItemResource::collection($paginator->items()),
            $pagination,
        );
    }

    /**
     * Show stock for a specific product across all warehouses.
     *
     * @param  string  $productId
     * @return JsonResponse
     */
    public function show(string $productId): JsonResponse
    {
        $items = $this->stockService->getProductStock($productId);

        return ApiResponse::success(StockItemResource::collection($items));
    }

    // -------------------------------------------------------------------------
    // Movements
    // -------------------------------------------------------------------------

    /**
     * Receive stock into a warehouse.
     *
     * @param  StockReceiveRequest  $request
     * @return JsonResponse
     */
    public function receive(StockReceiveRequest $request): JsonResponse
    {
        try {
            $data   = $request->validated();
            $claims = $request->attributes->get('jwt_claims', []);
            $data['performed_by'] = $claims['user_id'] ?? null;

            $ledger = $this->stockService->receive($data);

            return ApiResponse::created(new StockLedgerResource($ledger), 'Stock received successfully.');
        } catch (DomainException $e) {
            return ApiResponse::error($e->getMessage(), [], 422);
        } catch (ValidationException $e) {
            return ApiResponse::validationError($e->getErrors(), $e->getMessage());
        }
    }

    /**
     * Dispatch stock from a warehouse.
     *
     * @param  StockDispatchRequest  $request
     * @return JsonResponse
     */
    public function dispatch(StockDispatchRequest $request): JsonResponse
    {
        try {
            $data   = $request->validated();
            $claims = $request->attributes->get('jwt_claims', []);
            $data['performed_by'] = $claims['user_id'] ?? null;

            $ledger = $this->stockService->dispatch($data);

            return ApiResponse::created(new StockLedgerResource($ledger), 'Stock dispatched successfully.');
        } catch (DomainException $e) {
            return ApiResponse::error($e->getMessage(), [], 422);
        } catch (ValidationException $e) {
            return ApiResponse::validationError($e->getErrors(), $e->getMessage());
        }
    }

    /**
     * Adjust stock (positive or negative correction).
     *
     * @param  StockAdjustmentRequest  $request
     * @return JsonResponse
     */
    public function adjust(StockAdjustmentRequest $request): JsonResponse
    {
        try {
            $data   = $request->validated();
            $claims = $request->attributes->get('jwt_claims', []);
            $data['performed_by'] = $claims['user_id'] ?? null;

            $ledger = $this->stockService->adjust($data);

            return ApiResponse::created(new StockLedgerResource($ledger), 'Stock adjusted successfully.');
        } catch (DomainException $e) {
            return ApiResponse::error($e->getMessage(), [], 422);
        } catch (ValidationException $e) {
            return ApiResponse::validationError($e->getErrors(), $e->getMessage());
        }
    }

    /**
     * Transfer stock between warehouses or bins.
     *
     * @param  StockTransferRequest  $request
     * @return JsonResponse
     */
    public function transfer(StockTransferRequest $request): JsonResponse
    {
        try {
            $data   = $request->validated();
            $claims = $request->attributes->get('jwt_claims', []);
            $data['performed_by']   = $claims['user_id'] ?? null;
            $data['organization_id'] = $claims['organization_id'] ?? null;

            $transfer = $this->stockService->transfer($data);

            return ApiResponse::created($transfer, 'Stock transferred successfully.');
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        } catch (DomainException $e) {
            return ApiResponse::error($e->getMessage(), [], 422);
        }
    }

    // -------------------------------------------------------------------------
    // Reservations
    // -------------------------------------------------------------------------

    /**
     * List reservations.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function indexReservations(Request $request): JsonResponse
    {
        $perPage = $this->resolvePerPage($request);
        $page    = max(1, (int) $request->query('page', 1));
        $filters = $request->only(['product_id', 'warehouse_id', 'status', 'reference_type', 'reference_id']);

        $paginator  = $this->stockService->listReservations($filters, $page, $perPage);
        $pagination = $this->buildPagination($paginator);

        return ApiResponse::paginated(
            StockReservationResource::collection($paginator->items()),
            $pagination,
        );
    }

    /**
     * Reserve stock for an order.
     *
     * @param  StockReservationRequest  $request
     * @return JsonResponse
     */
    public function reserve(StockReservationRequest $request): JsonResponse
    {
        try {
            $data   = $request->validated();
            $claims = $request->attributes->get('jwt_claims', []);
            $data['performed_by'] = $claims['user_id'] ?? null;

            $reservation = $this->stockService->reserve($data);

            return ApiResponse::created(
                new StockReservationResource($reservation),
                'Stock reserved successfully.',
            );
        } catch (DomainException $e) {
            return ApiResponse::error($e->getMessage(), [], 422);
        }
    }

    /**
     * Release a stock reservation.
     *
     * @param  string  $reservation
     * @return JsonResponse
     */
    public function releaseReservation(string $reservation): JsonResponse
    {
        try {
            $this->stockService->releaseReservation($reservation);

            return ApiResponse::noContent();
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        } catch (DomainException $e) {
            return ApiResponse::error($e->getMessage(), [], 422);
        }
    }

    // -------------------------------------------------------------------------
    // Ledger
    // -------------------------------------------------------------------------

    /**
     * Query stock ledger history.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function ledger(Request $request): JsonResponse
    {
        $perPage = $this->resolvePerPage($request);
        $page    = max(1, (int) $request->query('page', 1));
        $filters = $request->only([
            'product_id', 'warehouse_id', 'transaction_type',
            'reference_type', 'reference_id', 'from_date', 'to_date',
        ]);

        $paginator  = $this->stockService->getLedger($filters, $page, $perPage);
        $pagination = $this->buildPagination($paginator);

        return ApiResponse::paginated(
            StockLedgerResource::collection($paginator->items()),
            $pagination,
        );
    }

    // -------------------------------------------------------------------------
    // Reorder Rules
    // -------------------------------------------------------------------------

    /**
     * List reorder rules.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function indexReorderRules(Request $request): JsonResponse
    {
        $perPage = $this->resolvePerPage($request);
        $page    = max(1, (int) $request->query('page', 1));
        $filters = $request->only(['product_id', 'warehouse_id', 'is_active']);

        $paginator  = $this->stockService->listReorderRules($filters, $page, $perPage);
        $pagination = $this->buildPagination($paginator);

        return ApiResponse::paginated($paginator->items(), $pagination);
    }

    /**
     * Create or update a reorder rule.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function storeReorderRule(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id'           => 'required|uuid',
            'warehouse_id'         => 'required|uuid',
            'reorder_point'        => 'required|numeric|min:0',
            'reorder_qty'          => 'required|numeric|min:0.0001',
            'max_qty'              => 'nullable|numeric|min:0',
            'safety_stock'         => 'nullable|numeric|min:0',
            'lead_time_days'       => 'nullable|integer|min:0',
            'is_active'            => 'nullable|boolean',
            'preferred_supplier_id' => 'nullable|uuid',
        ]);

        $rule = $this->stockService->upsertReorderRule($data);

        return ApiResponse::created($rule, 'Reorder rule saved successfully.');
    }

    /**
     * Update a reorder rule.
     *
     * @param  Request  $request
     * @param  string   $rule
     * @return JsonResponse
     */
    public function updateReorderRule(Request $request, string $rule): JsonResponse
    {
        $data = $request->validate([
            'reorder_point'  => 'sometimes|numeric|min:0',
            'reorder_qty'    => 'sometimes|numeric|min:0.0001',
            'max_qty'        => 'nullable|numeric|min:0',
            'safety_stock'   => 'nullable|numeric|min:0',
            'lead_time_days' => 'nullable|integer|min:0',
            'is_active'      => 'nullable|boolean',
        ]);

        try {
            $updated = $this->stockService->updateReorderRule($rule, $data);

            return ApiResponse::success($updated, 'Reorder rule updated successfully.');
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        }
    }

    /**
     * Delete a reorder rule.
     *
     * @param  string  $rule
     * @return JsonResponse
     */
    public function destroyReorderRule(string $rule): JsonResponse
    {
        try {
            $this->stockService->deleteReorderRule($rule);

            return ApiResponse::noContent();
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // Cycle Counts
    // -------------------------------------------------------------------------

    /**
     * List cycle counts.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function indexCycleCounts(Request $request): JsonResponse
    {
        $perPage = $this->resolvePerPage($request);
        $page    = max(1, (int) $request->query('page', 1));
        $filters = $request->only(['warehouse_id', 'status', 'count_type']);

        $paginator  = $this->stockService->listCycleCounts($filters, $page, $perPage);
        $pagination = $this->buildPagination($paginator);

        return ApiResponse::paginated($paginator->items(), $pagination);
    }

    /**
     * Open a new cycle count.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function storeCycleCount(Request $request): JsonResponse
    {
        $data = $request->validate([
            'warehouse_id'  => 'required|uuid',
            'count_type'    => 'nullable|string|in:full,partial,abc_class,zone,random',
            'count_number'  => 'nullable|string|max:50',
            'scheduled_at'  => 'nullable|date',
            'notes'         => 'nullable|string',
            'product_ids'   => 'nullable|array',
            'product_ids.*' => 'uuid',
        ]);

        $claims = $request->attributes->get('jwt_claims', []);
        $data['performed_by'] = $claims['user_id'] ?? null;

        try {
            $cycleCount = $this->stockService->openCycleCount($data);

            return ApiResponse::created($cycleCount, 'Cycle count opened successfully.');
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        }
    }

    /**
     * Show a cycle count with its lines.
     *
     * @param  string  $count
     * @return JsonResponse
     */
    public function showCycleCount(string $count): JsonResponse
    {
        try {
            $cycleCount = $this->stockService->findCycleCountOrFail($count);

            return ApiResponse::success($cycleCount);
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        }
    }

    /**
     * Confirm a cycle count and post variance adjustments.
     *
     * @param  Request  $request
     * @param  string   $count
     * @return JsonResponse
     */
    public function confirmCycleCount(Request $request, string $count): JsonResponse
    {
        $data = $request->validate([
            'lines'              => 'required|array|min:1',
            'lines.*.line_id'    => 'required|uuid',
            'lines.*.counted_qty' => 'required|numeric|min:0',
        ]);

        try {
            $cycleCount = $this->stockService->confirmCycleCount($count, $data['lines']);

            return ApiResponse::success($cycleCount, 'Cycle count confirmed successfully.');
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        } catch (DomainException $e) {
            return ApiResponse::error($e->getMessage(), [], 422);
        }
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Resolve per-page limit from the request, capped at the configured max.
     *
     * @param  Request  $request
     * @return int
     */
    private function resolvePerPage(Request $request): int
    {
        return min(
            (int) $request->query('per_page', config('inventory_service.pagination.default_per_page', 15)),
            (int) config('inventory_service.pagination.max_per_page', 100),
        );
    }

    /**
     * Build a PaginationDTO from a LengthAwarePaginator.
     *
     * @param  \Illuminate\Pagination\LengthAwarePaginator<mixed>  $paginator
     * @return PaginationDTO
     */
    private function buildPagination(\Illuminate\Pagination\LengthAwarePaginator $paginator): PaginationDTO
    {
        return new PaginationDTO(
            page:     $paginator->currentPage(),
            perPage:  $paginator->perPage(),
            total:    $paginator->total(),
            lastPage: $paginator->lastPage(),
            from:     $paginator->firstItem() ?? 0,
            to:       $paginator->lastItem() ?? 0,
        );
    }
}

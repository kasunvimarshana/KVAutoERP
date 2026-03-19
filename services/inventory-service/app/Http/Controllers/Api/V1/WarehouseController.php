<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Contracts\Services\WarehouseServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBinRequest;
use App\Http\Requests\StoreWarehouseRequest;
use App\Http\Requests\UpdateWarehouseRequest;
use App\Http\Resources\WarehouseResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use KvEnterprise\SharedKernel\DTOs\PaginationDTO;
use KvEnterprise\SharedKernel\Exceptions\DomainException;
use KvEnterprise\SharedKernel\Exceptions\NotFoundException;
use KvEnterprise\SharedKernel\Exceptions\ValidationException;
use KvEnterprise\SharedKernel\Http\Responses\ApiResponse;

/**
 * Warehouse resource controller (v1).
 *
 * Thin controller — delegates all business logic to WarehouseService.
 */
final class WarehouseController extends Controller
{
    public function __construct(
        private readonly WarehouseServiceInterface $warehouseService,
    ) {}

    /**
     * List warehouses.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min(
            (int) $request->query('per_page', config('inventory_service.pagination.default_per_page', 15)),
            (int) config('inventory_service.pagination.max_per_page', 100),
        );
        $page    = max(1, (int) $request->query('page', 1));
        $filters = $request->only(['search', 'status', 'type', 'sort_by', 'sort_dir']);

        $paginator = $this->warehouseService->list($filters, $page, $perPage);

        $pagination = new PaginationDTO(
            page:     $paginator->currentPage(),
            perPage:  $paginator->perPage(),
            total:    $paginator->total(),
            lastPage: $paginator->lastPage(),
            from:     $paginator->firstItem() ?? 0,
            to:       $paginator->lastItem() ?? 0,
        );

        return ApiResponse::paginated(
            WarehouseResource::collection($paginator->items()),
            $pagination,
        );
    }

    /**
     * Create a new warehouse.
     *
     * @param  StoreWarehouseRequest  $request
     * @return JsonResponse
     */
    public function store(StoreWarehouseRequest $request): JsonResponse
    {
        try {
            $warehouse = $this->warehouseService->create($request->validated());

            return ApiResponse::created(new WarehouseResource($warehouse), 'Warehouse created successfully.');
        } catch (ValidationException $e) {
            return ApiResponse::validationError($e->getErrors(), $e->getMessage());
        }
    }

    /**
     * Show a single warehouse.
     *
     * @param  string  $warehouse
     * @return JsonResponse
     */
    public function show(string $warehouse): JsonResponse
    {
        try {
            $model = $this->warehouseService->findOrFail($warehouse);

            return ApiResponse::success(new WarehouseResource($model));
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        }
    }

    /**
     * Update a warehouse.
     *
     * @param  UpdateWarehouseRequest  $request
     * @param  string                  $warehouse
     * @return JsonResponse
     */
    public function update(UpdateWarehouseRequest $request, string $warehouse): JsonResponse
    {
        try {
            $model = $this->warehouseService->update($warehouse, $request->validated());

            return ApiResponse::success(new WarehouseResource($model), 'Warehouse updated successfully.');
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        } catch (ValidationException $e) {
            return ApiResponse::validationError($e->getErrors(), $e->getMessage());
        }
    }

    /**
     * Soft-delete a warehouse.
     *
     * @param  string  $warehouse
     * @return JsonResponse
     */
    public function destroy(string $warehouse): JsonResponse
    {
        try {
            $this->warehouseService->delete($warehouse);

            return ApiResponse::noContent();
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        } catch (DomainException $e) {
            return ApiResponse::error($e->getMessage(), [], 422);
        }
    }

    /**
     * List bins for a warehouse.
     *
     * @param  string  $warehouse
     * @return JsonResponse
     */
    public function indexBins(string $warehouse): JsonResponse
    {
        try {
            $bins = $this->warehouseService->getBins($warehouse);

            return ApiResponse::success($bins);
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        }
    }

    /**
     * Create a bin within a warehouse.
     *
     * @param  StoreBinRequest  $request
     * @param  string           $warehouse
     * @return JsonResponse
     */
    public function storeBin(StoreBinRequest $request, string $warehouse): JsonResponse
    {
        try {
            $bin = $this->warehouseService->createBin($warehouse, $request->validated());

            return ApiResponse::created($bin, 'Bin created successfully.');
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        } catch (ValidationException $e) {
            return ApiResponse::validationError($e->getErrors(), $e->getMessage());
        }
    }

    /**
     * Update a bin.
     *
     * @param  Request  $request
     * @param  string   $warehouse
     * @param  string   $bin
     * @return JsonResponse
     */
    public function updateBin(Request $request, string $warehouse, string $bin): JsonResponse
    {
        $data = $request->validate([
            'code'     => 'sometimes|string|max:50',
            'name'     => 'nullable|string|max:255',
            'zone'     => 'nullable|string|max:50',
            'type'     => 'nullable|string|in:' . implode(',', \App\Models\WarehouseBin::TYPES),
            'status'   => 'nullable|string|in:' . implode(',', \App\Models\WarehouseBin::STATUSES),
            'capacity' => 'nullable|numeric|min:0',
        ]);

        try {
            $updatedBin = $this->warehouseService->updateBin($warehouse, $bin, $data);

            return ApiResponse::success($updatedBin, 'Bin updated successfully.');
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        }
    }

    /**
     * Delete a bin.
     *
     * @param  string  $warehouse
     * @param  string  $bin
     * @return JsonResponse
     */
    public function destroyBin(string $warehouse, string $bin): JsonResponse
    {
        try {
            $this->warehouseService->deleteBin($warehouse, $bin);

            return ApiResponse::noContent();
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        }
    }
}

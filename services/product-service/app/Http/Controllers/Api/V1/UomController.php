<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Contracts\Services\UomServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUomConversionRequest;
use App\Http\Requests\StoreUomRequest;
use App\Http\Resources\UomResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use KvEnterprise\SharedKernel\DTOs\PaginationDTO;
use KvEnterprise\SharedKernel\Exceptions\NotFoundException;
use KvEnterprise\SharedKernel\Exceptions\ValidationException;
use KvEnterprise\SharedKernel\Http\Responses\ApiResponse;

/**
 * Unit of Measure controller (v1).
 */
final class UomController extends Controller
{
    public function __construct(
        private readonly UomServiceInterface $uomService,
    ) {}

    /**
     * List UOMs with optional search and pagination.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 15), 100);
        $page    = max(1, (int) $request->query('page', 1));
        $filters = $request->only(['search', 'category', 'is_base_unit']);

        $paginator = $this->uomService->list($filters, $page, $perPage);

        $pagination = new PaginationDTO(
            page:     $paginator->currentPage(),
            perPage:  $paginator->perPage(),
            total:    $paginator->total(),
            lastPage: $paginator->lastPage(),
            from:     $paginator->firstItem() ?? 0,
            to:       $paginator->lastItem() ?? 0,
        );

        return ApiResponse::paginated(
            UomResource::collection($paginator->items()),
            $pagination,
        );
    }

    /**
     * Create a new UOM.
     *
     * @param  StoreUomRequest  $request
     * @return JsonResponse
     */
    public function store(StoreUomRequest $request): JsonResponse
    {
        $uom = $this->uomService->create($request->validated());

        return ApiResponse::created(new UomResource($uom), 'Unit of measure created successfully.');
    }

    /**
     * Show a single UOM.
     *
     * @param  string  $uom
     * @return JsonResponse
     */
    public function show(string $uom): JsonResponse
    {
        try {
            $model = $this->uomService->findOrFail($uom);

            return ApiResponse::success(new UomResource($model));
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        }
    }

    /**
     * Update a UOM.
     *
     * @param  StoreUomRequest  $request
     * @param  string           $uom
     * @return JsonResponse
     */
    public function update(StoreUomRequest $request, string $uom): JsonResponse
    {
        try {
            $model = $this->uomService->update($uom, $request->validated());

            return ApiResponse::success(new UomResource($model), 'Unit of measure updated successfully.');
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        }
    }

    /**
     * Delete a UOM.
     *
     * @param  string  $uom
     * @return JsonResponse
     */
    public function destroy(string $uom): JsonResponse
    {
        try {
            $this->uomService->delete($uom);

            return ApiResponse::noContent();
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        }
    }

    /**
     * Convert a quantity between two UOMs.
     *
     * Query / body params: from_uom_id, to_uom_id, amount.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function convert(Request $request): JsonResponse
    {
        $data = $request->validate([
            'from_uom_id' => 'required|uuid',
            'to_uom_id'   => 'required|uuid',
            'amount'      => 'required|numeric',
        ]);

        try {
            $result = $this->uomService->convert(
                $data['from_uom_id'],
                $data['to_uom_id'],
                (string) $data['amount'],
            );

            return ApiResponse::success([
                'from_uom_id' => $data['from_uom_id'],
                'to_uom_id'   => $data['to_uom_id'],
                'amount'      => (string) $data['amount'],
                'result'      => $result,
            ]);
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        } catch (ValidationException $e) {
            return ApiResponse::validationError($e->getErrors(), $e->getMessage());
        }
    }

    /**
     * Store a conversion rule for a UOM.
     *
     * @param  StoreUomConversionRequest  $request
     * @param  string                     $uom
     * @return JsonResponse
     */
    public function storeConversion(StoreUomConversionRequest $request, string $uom): JsonResponse
    {
        try {
            $conversion = $this->uomService->upsertConversion(
                $uom,
                $request->validated('to_uom_id'),
                (string) $request->validated('factor'),
            );

            return ApiResponse::created([
                'id'          => $conversion->id,
                'from_uom_id' => $conversion->from_uom_id,
                'to_uom_id'   => $conversion->to_uom_id,
                'factor'      => $conversion->factor,
            ], 'Conversion rule saved successfully.');
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        } catch (ValidationException $e) {
            return ApiResponse::validationError($e->getErrors(), $e->getMessage());
        }
    }

    /**
     * List all conversions defined for a UOM.
     *
     * @param  string  $uom
     * @return JsonResponse
     */
    public function indexConversions(string $uom): JsonResponse
    {
        try {
            $this->uomService->findOrFail($uom);
            $conversions = $this->uomService->getConversions($uom);

            return ApiResponse::success($conversions->map(static fn ($c) => [
                'id'          => $c->id,
                'from_uom_id' => $c->from_uom_id,
                'to_uom_id'   => $c->to_uom_id,
                'factor'      => $c->factor,
            ])->values());
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        }
    }
}

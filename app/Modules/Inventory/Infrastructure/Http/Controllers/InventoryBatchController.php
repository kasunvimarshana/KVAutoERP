<?php
namespace Modules\Inventory\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Inventory\Application\Contracts\CreateInventoryBatchServiceInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\InventoryBatchRepositoryInterface;
use Modules\Inventory\Infrastructure\Http\Resources\InventoryBatchResource;

class InventoryBatchController extends Controller
{
    public function __construct(
        private readonly InventoryBatchRepositoryInterface $repository,
        private readonly CreateInventoryBatchServiceInterface $createService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = (int) $request->query('tenant_id', 0);
        $productId = (int) $request->query('product_id', 0);

        if ($productId > 0 && $tenantId > 0) {
            $batches = [];
            // Return empty paginated-style result when no specific lookup method available
        }

        return response()->json([]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'tenant_id'    => 'required|integer',
            'product_id'   => 'required|integer',
            'batch_number' => 'required|string',
            'status'       => 'sometimes|string',
        ]);

        $batch = $this->createService->execute($request->all());
        return response()->json(new InventoryBatchResource($batch), 201);
    }

    public function show(int $id): JsonResponse
    {
        $batch = $this->repository->findById($id);
        if (!$batch) return response()->json(['message' => 'Not found'], 404);
        return response()->json(new InventoryBatchResource($batch));
    }
}

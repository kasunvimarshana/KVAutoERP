<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Inventory\Application\Contracts\StockServiceInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\StockRepositoryInterface;

class StockController extends \Illuminate\Routing\Controller
{
    public function __construct(
        private readonly StockRepositoryInterface $stockRepo,
        private readonly StockServiceInterface $stockService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);
        $stocks   = $this->stockRepo->allByTenant($tenantId);

        return response()->json(['data' => $stocks]);
    }

    public function show(int $id, Request $request): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);
        $stock    = $this->stockRepo->findById($id, $tenantId);

        if ($stock === null) {
            return response()->json(['message' => 'Stock not found.'], 404);
        }

        return response()->json(['data' => $stock]);
    }
}

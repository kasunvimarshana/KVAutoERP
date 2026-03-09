<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Inventory\Queries\GetInventoryReportQuery;
use App\Services\InventoryService;
use App\Domain\Inventory\Repositories\StockMovementRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ReportController extends Controller
{
    public function __construct(
        private readonly InventoryService $inventoryService,
        private readonly StockMovementRepositoryInterface $movementRepository,
    ) {}

    public function inventoryReport(Request $request): JsonResponse
    {
        $tenantId = $request->get('_tenant_id', $request->header('X-Tenant-ID'));
        $report   = $this->inventoryService->getInventoryReport(new GetInventoryReportQuery(
            tenantId: $tenantId,
            warehouseId: $request->input('warehouse_id'),
            fromDate: $request->input('from'),
            toDate: $request->input('to'),
            groupBy: $request->input('group_by', 'category'),
        ));
        return response()->json(['data' => $report]);
    }

    public function lowStockReport(Request $request): JsonResponse
    {
        $tenantId = $request->get('_tenant_id', $request->header('X-Tenant-ID'));
        $products = $this->inventoryService->getLowStockAlert($tenantId);
        return response()->json(['data' => $products, 'count' => count($products)]);
    }

    public function movementReport(Request $request): JsonResponse
    {
        $request->validate(['product_id' => 'required|uuid', 'from' => 'required|date', 'to' => 'required|date']);
        $summary = $this->movementRepository->getMovementSummary(
            $request->input('product_id'),
            new \DateTimeImmutable($request->input('from')),
            new \DateTimeImmutable($request->input('to')),
        );
        return response()->json(['data' => $summary]);
    }

    public function valuationReport(Request $request): JsonResponse
    {
        $tenantId = $request->get('_tenant_id', $request->header('X-Tenant-ID'));
        $report   = $this->inventoryService->getInventoryReport(new GetInventoryReportQuery(
            tenantId: $tenantId,
            groupBy: 'category',
        ));
        return response()->json(['data' => [
            'total_value'      => $report['total_value'],
            'total_cost_value' => $report['total_cost_value'],
            'gross_margin_pct' => $report['gross_margin'],
            'breakdown'        => $report['grouped'],
            'generated_at'     => $report['generated_at'],
        ]]);
    }
}

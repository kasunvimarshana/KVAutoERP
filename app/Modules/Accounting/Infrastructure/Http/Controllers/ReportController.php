<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Accounting\Application\Contracts\GenerateFinancialReportServiceInterface;

class ReportController extends Controller
{
    public function __construct(private readonly GenerateFinancialReportServiceInterface $service) {}

    public function balanceSheet(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? $request->user()?->tenant_id;
        $asOf = $request->query('as_of', now()->toDateString());
        return response()->json($this->service->generateBalanceSheet($tenantId, $asOf));
    }

    public function profitLoss(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? $request->user()?->tenant_id;
        $from = $request->query('from', now()->startOfYear()->toDateString());
        $to   = $request->query('to', now()->toDateString());
        return response()->json($this->service->generateProfitLoss($tenantId, $from, $to));
    }

    public function cashFlow(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? $request->user()?->tenant_id;
        $from = $request->query('from', now()->startOfMonth()->toDateString());
        $to   = $request->query('to', now()->toDateString());
        return response()->json($this->service->generateCashFlow($tenantId, $from, $to));
    }
}

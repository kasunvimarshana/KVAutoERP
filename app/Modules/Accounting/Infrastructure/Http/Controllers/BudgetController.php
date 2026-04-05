<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Accounting\Application\Contracts\BudgetServiceInterface;
use Modules\Accounting\Domain\Entities\Budget;
use Modules\Core\Domain\Exceptions\NotFoundException;

class BudgetController extends Controller
{
    public function __construct(private readonly BudgetServiceInterface $service) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? $request->user()?->tenant_id;
        return response()->json($this->service->getAll($tenantId)->map(fn(Budget $b) => $this->serialize($b))->values());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['tenant_id' => 'required|uuid', 'name' => 'required|string', 'fiscal_year' => 'required|integer', 'account_id' => 'required|uuid', 'amount' => 'required|numeric|min:0', 'period' => 'nullable|in:monthly,quarterly,annual', 'start_date' => 'required|date', 'end_date' => 'required|date|after:start_date', 'notes' => 'nullable|string']);
        return response()->json($this->serialize($this->service->createBudget($data)), 201);
    }

    public function show(string $id): JsonResponse
    {
        try { return response()->json($this->serialize($this->service->getBudget($id))); }
        catch (NotFoundException $e) { return response()->json(['message' => $e->getMessage()], 404); }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $data = $request->validate(['name' => 'sometimes|string', 'amount' => 'sometimes|numeric|min:0']);
        try { return response()->json($this->serialize($this->service->updateBudget($id, $data))); }
        catch (NotFoundException $e) { return response()->json(['message' => $e->getMessage()], 404); }
    }

    public function destroy(string $id): JsonResponse
    {
        try { $this->service->deleteBudget($id); return response()->json(null, 204); }
        catch (NotFoundException $e) { return response()->json(['message' => $e->getMessage()], 404); }
    }

    public function vsActual(string $id): JsonResponse
    {
        try { return response()->json($this->service->getBudgetVsActual($id)); }
        catch (NotFoundException $e) { return response()->json(['message' => $e->getMessage()], 404); }
    }

    private function serialize(Budget $b): array
    {
        return ['id' => $b->getId(), 'name' => $b->getName(), 'fiscal_year' => $b->getFiscalYear(), 'account_id' => $b->getAccountId(), 'amount' => $b->getAmount(), 'period' => $b->getPeriod()];
    }
}

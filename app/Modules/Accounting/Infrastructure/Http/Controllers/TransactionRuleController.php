<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Accounting\Domain\Entities\TransactionRule;
use Modules\Accounting\Domain\RepositoryInterfaces\TransactionRuleRepositoryInterface;

class TransactionRuleController extends Controller
{
    public function __construct(private readonly TransactionRuleRepositoryInterface $repo) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? $request->user()?->tenant_id;
        return response()->json($this->repo->allByTenant($tenantId)->map(fn(TransactionRule $r) => $this->serialize($r))->values());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['tenant_id' => 'required|uuid', 'name' => 'required|string', 'conditions' => 'required|array', 'category_id' => 'nullable|uuid', 'apply_to' => 'nullable|in:all,debit,credit', 'priority' => 'nullable|integer']);
        return response()->json($this->serialize($this->repo->create($data)), 201);
    }

    public function show(string $id): JsonResponse
    {
        $r = $this->repo->findById($id);
        return $r ? response()->json($this->serialize($r)) : response()->json(['message' => 'Not found'], 404);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $data = $request->validate(['name' => 'sometimes|string', 'is_active' => 'nullable|boolean', 'priority' => 'nullable|integer']);
        return response()->json($this->serialize($this->repo->update($id, $data)));
    }

    public function destroy(string $id): JsonResponse
    {
        $this->repo->delete($id);
        return response()->json(null, 204);
    }

    private function serialize(TransactionRule $r): array
    {
        return ['id' => $r->getId(), 'name' => $r->getName(), 'conditions' => $r->getConditions(), 'apply_to' => $r->getApplyTo(), 'priority' => $r->getPriority(), 'is_active' => $r->isActive()];
    }
}

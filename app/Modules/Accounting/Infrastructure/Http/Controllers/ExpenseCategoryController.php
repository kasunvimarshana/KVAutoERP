<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Accounting\Domain\Entities\ExpenseCategory;
use Modules\Accounting\Domain\RepositoryInterfaces\ExpenseCategoryRepositoryInterface;

class ExpenseCategoryController extends Controller
{
    public function __construct(private readonly ExpenseCategoryRepositoryInterface $repo) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? $request->user()?->tenant_id;
        return response()->json($this->repo->allByTenant($tenantId)->map(fn(ExpenseCategory $c) => $this->serialize($c))->values());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['tenant_id' => 'required|uuid', 'name' => 'required|string', 'code' => 'required|string', 'parent_id' => 'nullable|uuid', 'account_id' => 'nullable|uuid']);
        return response()->json($this->serialize($this->repo->create($data)), 201);
    }

    public function show(string $id): JsonResponse
    {
        $c = $this->repo->findById($id);
        return $c ? response()->json($this->serialize($c)) : response()->json(['message' => 'Not found'], 404);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $data = $request->validate(['name' => 'sometimes|string', 'is_active' => 'nullable|boolean']);
        return response()->json($this->serialize($this->repo->update($id, $data)));
    }

    public function destroy(string $id): JsonResponse
    {
        $this->repo->delete($id);
        return response()->json(null, 204);
    }

    private function serialize(ExpenseCategory $c): array
    {
        return ['id' => $c->getId(), 'name' => $c->getName(), 'code' => $c->getCode(), 'parent_id' => $c->getParentId(), 'is_active' => $c->isActive()];
    }
}

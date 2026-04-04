<?php

declare(strict_types=1);

namespace Modules\Configuration\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Configuration\Application\Contracts\CreateOrgUnitServiceInterface;
use Modules\Configuration\Application\Contracts\DeleteOrgUnitServiceInterface;
use Modules\Configuration\Application\Contracts\GetOrgUnitServiceInterface;
use Modules\Configuration\Application\Contracts\ListOrgUnitsServiceInterface;
use Modules\Configuration\Application\Contracts\OrgUnitTreeServiceInterface;
use Modules\Configuration\Application\Contracts\UpdateOrgUnitServiceInterface;
use Modules\Configuration\Application\DTOs\CreateOrgUnitData;
use Modules\Configuration\Application\DTOs\UpdateOrgUnitData;
use Modules\Core\Infrastructure\Http\Controllers\AuthorizedController;

class OrgUnitController extends AuthorizedController
{
    public function __construct(
        private readonly CreateOrgUnitServiceInterface $createService,
        private readonly UpdateOrgUnitServiceInterface $updateService,
        private readonly DeleteOrgUnitServiceInterface $deleteService,
        private readonly GetOrgUnitServiceInterface $getService,
        private readonly ListOrgUnitsServiceInterface $listService,
        private readonly OrgUnitTreeServiceInterface $treeService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', $request->user()?->tenant_id ?? 0);
        $units = $this->listService->execute($tenantId);

        return response()->json($units);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id'   => ['required', 'integer'],
            'name'        => ['required', 'string', 'max:255'],
            'parent_id'   => ['nullable', 'integer'],
            'code'        => ['nullable', 'string', 'max:100'],
            'type'        => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['nullable', 'boolean'],
            'sort_order'  => ['nullable', 'integer'],
        ]);

        $data = new CreateOrgUnitData(
            tenantId: (int) $validated['tenant_id'],
            name: $validated['name'],
            parentId: isset($validated['parent_id']) ? (int) $validated['parent_id'] : null,
            code: $validated['code'] ?? null,
            type: $validated['type'] ?? 'department',
            description: $validated['description'] ?? null,
            isActive: (bool) ($validated['is_active'] ?? true),
            sortOrder: (int) ($validated['sort_order'] ?? 0),
        );

        $orgUnit = $this->createService->execute($data);

        return response()->json($orgUnit, 201);
    }

    public function show(int $id): JsonResponse
    {
        $orgUnit = $this->getService->execute($id);

        return response()->json($orgUnit);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name'        => ['nullable', 'string', 'max:255'],
            'parent_id'   => ['nullable', 'integer'],
            'code'        => ['nullable', 'string', 'max:100'],
            'type'        => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['nullable', 'boolean'],
            'sort_order'  => ['nullable', 'integer'],
        ]);

        $data = new UpdateOrgUnitData(
            name: $validated['name'] ?? null,
            parentId: isset($validated['parent_id']) ? (int) $validated['parent_id'] : null,
            code: $validated['code'] ?? null,
            type: $validated['type'] ?? null,
            description: $validated['description'] ?? null,
            isActive: isset($validated['is_active']) ? (bool) $validated['is_active'] : null,
            sortOrder: isset($validated['sort_order']) ? (int) $validated['sort_order'] : null,
        );

        $orgUnit = $this->updateService->execute($id, $data);

        return response()->json($orgUnit);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->deleteService->execute($id);

        return response()->json(null, 204);
    }

    public function tree(Request $request): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', $request->user()?->tenant_id ?? 0);
        $tree = $this->treeService->execute($tenantId);

        return response()->json($tree);
    }
}

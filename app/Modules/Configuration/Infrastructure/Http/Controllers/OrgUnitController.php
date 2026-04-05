<?php

declare(strict_types=1);

namespace Modules\Configuration\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Configuration\Application\Contracts\OrgUnitServiceInterface;
use Modules\Configuration\Domain\Entities\OrgUnit;
use Modules\Core\Domain\Exceptions\DomainException;
use Modules\Core\Domain\Exceptions\NotFoundException;

class OrgUnitController extends Controller
{
    public function __construct(private readonly OrgUnitServiceInterface $service) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? $request->user()?->tenant_id;
        $units = $this->service->getTree($tenantId);

        return response()->json($units->map(fn (OrgUnit $u) => $this->serialize($u))->values());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tenant_id' => 'required|uuid',
            'name'      => 'required|string|max:255',
            'code'      => 'required|string|max:50',
            'type'      => 'required|in:company,division,department,team,branch,warehouse,costcenter,plant',
            'parent_id' => 'nullable|uuid',
            'is_active' => 'nullable|boolean',
            'metadata'  => 'nullable|array',
        ]);

        try {
            $unit = $this->service->createOrgUnit($data);

            return response()->json($this->serialize($unit), 201);
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            return response()->json($this->serialize($this->service->getOrgUnit($id)));
        } catch (NotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'name'      => 'sometimes|string|max:255',
            'code'      => 'sometimes|string|max:50',
            'type'      => 'sometimes|in:company,division,department,team,branch,warehouse,costcenter,plant',
            'is_active' => 'nullable|boolean',
            'metadata'  => 'nullable|array',
        ]);

        try {
            return response()->json($this->serialize($this->service->updateOrgUnit($id, $data)));
        } catch (NotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $this->service->deleteOrgUnit($id);

            return response()->json(null, 204);
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function move(Request $request, string $id): JsonResponse
    {
        $data = $request->validate(['parent_id' => 'nullable|uuid']);

        try {
            return response()->json($this->serialize($this->service->moveOrgUnit($id, $data['parent_id'] ?? null)));
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function descendants(string $id): JsonResponse
    {
        try {
            $units = $this->service->getDescendants($id);

            return response()->json($units->map(fn (OrgUnit $u) => $this->serialize($u))->values());
        } catch (NotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function ancestors(string $id): JsonResponse
    {
        try {
            $units = $this->service->getAncestors($id);

            return response()->json($units->map(fn (OrgUnit $u) => $this->serialize($u))->values());
        } catch (NotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    private function serialize(OrgUnit $u): array
    {
        return [
            'id'        => $u->getId(),
            'tenant_id' => $u->getTenantId(),
            'name'      => $u->getName(),
            'code'      => $u->getCode(),
            'type'      => $u->getType(),
            'parent_id' => $u->getParentId(),
            'path'      => $u->getPath(),
            'level'     => $u->getLevel(),
            'is_active' => $u->isActive(),
            'metadata'  => $u->getMetadata(),
        ];
    }
}

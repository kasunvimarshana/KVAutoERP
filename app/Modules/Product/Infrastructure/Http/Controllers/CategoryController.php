<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Product\Application\Contracts\CategoryServiceInterface;

class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryServiceInterface $service,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);

        return response()->json($this->service->getAll($tenantId));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:100',
            'parent_id'   => 'nullable|integer',
            'description' => 'nullable|string',
            'is_active'   => 'nullable|boolean',
        ]);

        $data['tenant_id'] = (int) $request->header('X-Tenant-ID', 0);

        $category = $this->service->createCategory($data);

        return response()->json($category, 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);

        return response()->json($this->service->getCategory($id, $tenantId));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'code'        => 'sometimes|string|max:100',
            'parent_id'   => 'nullable|integer',
            'description' => 'nullable|string',
            'is_active'   => 'nullable|boolean',
        ]);

        $data['tenant_id'] = (int) $request->header('X-Tenant-ID', 0);

        $category = $this->service->updateCategory($id, $data);

        return response()->json($category);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);
        $this->service->deleteCategory($id, $tenantId);

        return response()->json(null, 204);
    }

    public function tree(Request $request): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);

        return response()->json($this->service->getTree($tenantId));
    }
}

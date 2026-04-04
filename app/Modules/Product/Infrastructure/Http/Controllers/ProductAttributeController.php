<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Product\Domain\Repositories\ProductAttributeRepositoryInterface;
use Modules\Product\Infrastructure\Http\Resources\ProductAttributeResource;

class ProductAttributeController extends Controller
{
    public function __construct(
        private readonly ProductAttributeRepositoryInterface $repository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = (int) $request->get('tenant_id', 0);
        $attributes = $this->repository->findByTenant(
            $tenantId,
            (int) $request->get('per_page', 15),
            (int) $request->get('page', 1),
        );

        return response()->json([
            'data' => ProductAttributeResource::collection($attributes->items()),
            'meta' => [
                'current_page' => $attributes->currentPage(),
                'last_page'    => $attributes->lastPage(),
                'per_page'     => $attributes->perPage(),
                'total'        => $attributes->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'integer'],
            'name'      => ['required', 'string', 'max:255'],
            'slug'      => ['required', 'string', 'max:100'],
            'type'      => ['required', 'string', 'in:text,select,boolean,number'],
            'options'   => ['sometimes', 'nullable', 'array'],
        ]);

        $attribute = $this->repository->create([
            'tenant_id' => $validated['tenant_id'],
            'name'      => $validated['name'],
            'slug'      => $validated['slug'],
            'type'      => $validated['type'],
            'options'   => $validated['options'] ?? null,
        ]);

        return response()->json(new ProductAttributeResource($attribute), 201);
    }

    public function show(int $id): JsonResponse
    {
        $attribute = $this->repository->findById($id);
        if ($attribute === null) {
            return response()->json(['message' => 'Attribute not found.'], 404);
        }

        return response()->json(new ProductAttributeResource($attribute));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name'    => ['sometimes', 'string', 'max:255'],
            'slug'    => ['sometimes', 'string', 'max:100'],
            'type'    => ['sometimes', 'string', 'in:text,select,boolean,number'],
            'options' => ['sometimes', 'nullable', 'array'],
        ]);

        $updateData = array_filter($validated, fn ($v) => $v !== null);
        $attribute = $this->repository->update($id, $updateData);

        return response()->json(new ProductAttributeResource($attribute));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->repository->delete($id);

        return response()->json(null, 204);
    }
}

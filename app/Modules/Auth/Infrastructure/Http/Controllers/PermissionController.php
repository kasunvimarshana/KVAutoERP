<?php

declare(strict_types=1);

namespace Modules\Auth\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Auth\Domain\Repositories\PermissionRepositoryInterface;
use Modules\Auth\Infrastructure\Http\Requests\CreatePermissionRequest;
use Modules\Auth\Infrastructure\Http\Resources\PermissionResource;

class PermissionController extends Controller
{
    public function __construct(
        private readonly PermissionRepositoryInterface $repository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $permissions = $this->repository->findAll(
            (int) $request->get('per_page', 15),
            (int) $request->get('page', 1),
        );

        return response()->json([
            'data' => PermissionResource::collection($permissions->items()),
            'meta' => [
                'current_page' => $permissions->currentPage(),
                'last_page'    => $permissions->lastPage(),
                'per_page'     => $permissions->perPage(),
                'total'        => $permissions->total(),
            ],
        ]);
    }

    public function store(CreatePermissionRequest $request): JsonResponse
    {
        $permission = $this->repository->create($request->validated());

        return response()->json(new PermissionResource($permission), 201);
    }

    public function show(int $id): JsonResponse
    {
        $permission = $this->repository->findById($id);

        if ($permission === null) {
            return response()->json(['message' => 'Permission not found.'], 404);
        }

        return response()->json(new PermissionResource($permission));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->repository->delete($id);

        return response()->json(null, 204);
    }
}

<?php

declare(strict_types=1);

namespace Modules\Authorization\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Authorization\Domain\Entities\Permission;
use Modules\Authorization\Domain\RepositoryInterfaces\PermissionRepositoryInterface;
use Modules\Authorization\Infrastructure\Http\Resources\PermissionResource;
use Modules\Core\Infrastructure\Http\Controllers\AuthorizedController;

class PermissionController extends AuthorizedController
{
    public function __construct(
        private readonly PermissionRepositoryInterface $permissionRepository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $module = $request->query('module');
        $permissions = $module
            ? $this->permissionRepository->findByModule($module)
            : $this->permissionRepository->findAll();

        return response()->json([
            'data' => array_map(fn ($p) => (new PermissionResource($p))->toArray($request), $permissions),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $permission = $this->permissionRepository->findById($id);
        if ($permission === null) {
            return response()->json(['message' => 'Permission not found.'], 404);
        }

        return (new PermissionResource($permission))->response();
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:permissions,slug'],
            'module' => ['required', 'string', 'max:100'],
            'description' => ['sometimes', 'nullable', 'string'],
        ]);

        $permission = new Permission(
            id: null,
            name: $request->input('name'),
            slug: $request->input('slug'),
            module: $request->input('module'),
            description: $request->input('description'),
            createdAt: null,
            updatedAt: null,
        );

        $saved = $this->permissionRepository->save($permission);

        return (new PermissionResource($saved))->response()->setStatusCode(201);
    }
}

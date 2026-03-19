<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PermissionResource;
use App\Models\Permission;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PermissionController extends Controller
{
    use ApiResponse;

    public function __construct() {}

    public function index(): JsonResponse
    {
        $permissions = Permission::orderBy('group')->orderBy('name')->get();

        return $this->successResponse(PermissionResource::collection($permissions)->resolve());
    }

    public function show(string $id): JsonResponse
    {
        $permission = Permission::find($id);

        if (! $permission) {
            return $this->errorResponse('Permission not found', [], 404);
        }

        return $this->successResponse((new PermissionResource($permission))->resolve());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100', 'unique:permissions,name'],
            'slug'        => ['required', 'string', 'max:100', 'unique:permissions,slug'],
            'description' => ['sometimes', 'string'],
            'group'       => ['sometimes', 'string', 'max:100'],
        ]);

        $permission = Permission::create([
            'id'          => (string) Str::uuid(),
            'name'        => $data['name'],
            'slug'        => $data['slug'],
            'description' => $data['description'] ?? null,
            'group'       => $data['group'] ?? null,
        ]);

        return $this->successResponse(
            (new PermissionResource($permission))->resolve(),
            'Permission created successfully',
            201,
        );
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $permission = Permission::findOrFail($id);

        $data = $request->validate([
            'name'        => ['sometimes', 'string', 'max:100'],
            'description' => ['sometimes', 'string'],
            'group'       => ['sometimes', 'string', 'max:100'],
        ]);

        $permission->update($data);

        return $this->successResponse(
            (new PermissionResource($permission->fresh()))->resolve(),
            'Permission updated successfully',
        );
    }

    public function destroy(string $id): JsonResponse
    {
        Permission::findOrFail($id)->delete();

        return $this->successResponse(null, 'Permission deleted successfully');
    }
}

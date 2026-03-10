<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Domain\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class RoleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id') ?? $request->header('X-Tenant-ID');
        $roles = Role::where('tenant_id', $tenantId)
            ->active()
            ->paginate($request->input('per_page', 15));

        return response()->json(['success' => true, 'data' => $roles]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
            'is_active' => 'nullable|boolean',
        ]);

        $tenantId = $request->attributes->get('tenant_id') ?? $request->header('X-Tenant-ID');
        $role = Role::create(array_merge($validated, ['tenant_id' => $tenantId]));

        return response()->json(['success' => true, 'data' => $role], 201);
    }

    public function show(int $id): JsonResponse
    {
        $role = Role::findOrFail($id);
        return response()->json(['success' => true, 'data' => $role]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
            'permissions' => 'sometimes|nullable|array',
            'permissions.*' => 'string',
            'is_active' => 'sometimes|boolean',
        ]);
        $role = Role::findOrFail($id);
        $role->update($validated);
        return response()->json(['success' => true, 'data' => $role]);
    }

    public function destroy(int $id): JsonResponse
    {
        Role::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Role deleted.']);
    }
}

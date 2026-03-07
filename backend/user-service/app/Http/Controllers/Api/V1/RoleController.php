<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\RoleService;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    public function __construct(
        private readonly RoleService $roleService,
    ) {}

    public function index(): JsonResponse
    {
        return response()->json(['data' => $this->roleService->all()]);
    }

    public function show(string $name): JsonResponse
    {
        $role = $this->roleService->findByName($name);

        if (!$role) {
            return response()->json(['message' => 'Role not found.'], 404);
        }

        return response()->json(['data' => $role]);
    }
}

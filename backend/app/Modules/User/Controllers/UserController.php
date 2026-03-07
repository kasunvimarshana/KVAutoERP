<?php

namespace App\Modules\User\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\User\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private UserService $userService) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->userService->index($request->query()));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'is_active' => 'boolean',
            'roles'   => 'array',
            'roles.*' => 'string|exists:roles,name',
        ]);

        return response()->json($this->userService->store($request->all()), 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(
            $this->userService->show($id)->load('roles', 'permissions', 'tenant')
        );
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'name'     => 'sometimes|string|max:255',
            'email'    => 'sometimes|email|unique:users,email,'.$id,
            'password' => 'sometimes|string|min:8',
            'is_active' => 'sometimes|boolean',
            'roles'    => 'sometimes|array',
            'roles.*'  => 'string|exists:roles,name',
        ]);

        return response()->json($this->userService->update($id, $request->all()));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->userService->destroy($id);

        return response()->json(['message' => 'User deleted successfully.']);
    }

    public function assignRole(Request $request, int $id): JsonResponse
    {
        $request->validate(['role' => 'required|string|exists:roles,name']);

        return response()->json($this->userService->assignRole($id, $request->role));
    }

    public function revokeRole(Request $request, int $id): JsonResponse
    {
        $request->validate(['role' => 'required|string|exists:roles,name']);

        return response()->json($this->userService->revokeRole($id, $request->role));
    }
}

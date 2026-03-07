<?php

namespace App\Http\Controllers;

use App\DTOs\UserDTO;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public function __construct(private readonly UserService $userService) {}

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', \App\Models\User::class);

        $tenantId = $request->attributes->get('tenant_id');
        $perPage  = (int) $request->query('per_page', 15);
        $page     = (int) $request->query('page', 1);

        $filters = array_filter([
            'search' => $request->query('search'),
            'role'   => $request->query('role'),
            'status' => $request->query('status'),
        ]);

        $paginator = $this->userService->listUsers($tenantId, $filters, $perPage, $page);

        return response()->json([
            'success' => true,
            'data'    => collect($paginator->items())->map(
                fn ($u) => UserDTO::fromModel($u)->toArray()
            ),
            'message' => 'Users retrieved successfully.',
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
            ],
        ]);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');
        $user     = \App\Models\User::where('tenant_id', $tenantId)->find($id);

        if ($user === null) {
            return response()->json([
                'success' => false,
                'data'    => null,
                'message' => 'User not found.',
                'meta'    => [],
            ], 404);
        }

        Gate::authorize('view', $user);

        return response()->json([
            'success' => true,
            'data'    => UserDTO::fromModel($user)->toArray(),
            'message' => 'User retrieved successfully.',
            'meta'    => [],
        ]);
    }

    public function store(CreateUserRequest $request): JsonResponse
    {
        Gate::authorize('create', \App\Models\User::class);

        $tenantId = $request->attributes->get('tenant_id');

        try {
            $dto = $this->userService->createUser($tenantId, $request->validated());
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'data'    => null,
                'message' => 'Validation failed.',
                'meta'    => ['errors' => $e->errors()],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data'    => $dto->toArray(),
            'message' => 'User created successfully.',
            'meta'    => [],
        ], 201);
    }

    public function update(UpdateUserRequest $request, string $id): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');
        $user     = \App\Models\User::where('tenant_id', $tenantId)->find($id);

        if ($user === null) {
            return response()->json([
                'success' => false,
                'data'    => null,
                'message' => 'User not found.',
                'meta'    => [],
            ], 404);
        }

        Gate::authorize('update', $user);

        try {
            $dto = $this->userService->updateUser($tenantId, $id, $request->validated());
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'data'    => null,
                'message' => 'Validation failed.',
                'meta'    => ['errors' => $e->errors()],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data'    => $dto?->toArray(),
            'message' => 'User updated successfully.',
            'meta'    => [],
        ]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id');
        $user     = \App\Models\User::where('tenant_id', $tenantId)->find($id);

        if ($user === null) {
            return response()->json([
                'success' => false,
                'data'    => null,
                'message' => 'User not found.',
                'meta'    => [],
            ], 404);
        }

        Gate::authorize('delete', $user);

        $deleted = $this->userService->deleteUser($tenantId, $id);

        return response()->json([
            'success' => $deleted,
            'data'    => null,
            'message' => $deleted ? 'User deleted successfully.' : 'Failed to delete user.',
            'meta'    => [],
        ], $deleted ? 200 : 500);
    }

    public function assignRole(Request $request, string $id): JsonResponse
    {
        $request->validate(['role' => ['required', 'in:admin,manager,user']]);

        $tenantId = $request->attributes->get('tenant_id');
        $user     = \App\Models\User::where('tenant_id', $tenantId)->find($id);

        if ($user === null) {
            return response()->json([
                'success' => false,
                'data'    => null,
                'message' => 'User not found.',
                'meta'    => [],
            ], 404);
        }

        Gate::authorize('assignRole', $user);

        $dto = $this->userService->assignRole($tenantId, $id, $request->input('role'));

        return response()->json([
            'success' => true,
            'data'    => $dto?->toArray(),
            'message' => 'Role assigned successfully.',
            'meta'    => [],
        ]);
    }

    public function updatePermissions(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'permissions'   => ['required', 'array'],
            'permissions.*' => ['string'],
        ]);

        $tenantId = $request->attributes->get('tenant_id');
        $user     = \App\Models\User::where('tenant_id', $tenantId)->find($id);

        if ($user === null) {
            return response()->json([
                'success' => false,
                'data'    => null,
                'message' => 'User not found.',
                'meta'    => [],
            ], 404);
        }

        Gate::authorize('updatePermissions', $user);

        $dto = $this->userService->updatePermissions($tenantId, $id, $request->input('permissions'));

        return response()->json([
            'success' => true,
            'data'    => $dto?->toArray(),
            'message' => 'Permissions updated successfully.',
            'meta'    => [],
        ]);
    }
}

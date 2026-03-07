<?php

namespace App\Modules\User\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\User\DTOs\UserDTO;
use App\Modules\User\Requests\CreateUserRequest;
use App\Modules\User\Requests\UpdateUserRequest;
use App\Modules\User\Resources\UserResource;
use App\Modules\User\Resources\UserCollection;
use App\Modules\User\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    public function index(Request $request): UserCollection
    {
        $filters = $request->only([
            'search',
            'role',
            'is_active',
            'sort_by',
            'sort_direction',
        ]);

        $perPage = min((int) $request->input('per_page', 15), 100);
        $users   = $this->userService->listUsers($filters, $perPage);

        return new UserCollection($users);
    }

    public function show(int $id): JsonResponse
    {
        $user = $this->userService->getUser($id);
        return response()->json(new UserResource($user));
    }

    public function store(CreateUserRequest $request): JsonResponse
    {
        $dto  = UserDTO::fromRequest($request->validated());
        $user = $this->userService->createUser($dto);
        return response()->json(new UserResource($user), 201);
    }

    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        $dto  = UserDTO::fromRequest($request->validated());
        $user = $this->userService->updateUser($id, $dto);
        return response()->json(new UserResource($user));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->userService->deleteUser($id);
        return response()->json(null, 204);
    }

    /**
     * Check RBAC authorization for a user.
     */
    public function checkRole(Request $request, int $id): JsonResponse
    {
        $request->validate(['role' => ['required', 'string']]);

        $hasRole = $this->userService->userHasRole($id, $request->input('role'));
        return response()->json(['has_role' => $hasRole]);
    }

    /**
     * Check ABAC attribute for a user.
     */
    public function checkAttribute(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'key'   => ['required', 'string'],
            'value' => ['required'],
        ]);

        $hasAttribute = $this->userService->userHasAttribute(
            $id,
            $request->input('key'),
            $request->input('value')
        );

        return response()->json(['has_attribute' => $hasAttribute]);
    }
}

<?php

namespace App\Modules\User\Controllers;

use App\Modules\User\DTOs\UserDTO;
use App\Modules\User\Requests\CreateUserRequest;
use App\Modules\User\Requests\UpdateUserRequest;
use App\Modules\User\Resources\UserResource;
use App\Modules\User\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

class UserController extends Controller
{
    public function __construct(private UserService $userService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $tenantId = app('tenant_id');
        $perPage  = (int) $request->query('per_page', 15);
        $filters  = $request->only(['role', 'is_active', 'search']);

        $users = $this->userService->list($tenantId, $perPage, $filters);

        return UserResource::collection($users);
    }

    public function show(Request $request, string $id): UserResource
    {
        $tenantId = app('tenant_id');
        $user     = $this->userService->findById($id, $tenantId);

        return new UserResource($user);
    }

    public function store(CreateUserRequest $request): JsonResponse
    {
        $tenantId = app('tenant_id');
        $data     = $request->validated();
        $data['tenant_id'] = $tenantId;

        $dto  = UserDTO::fromRequest($data);
        $user = $this->userService->create($dto);

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateUserRequest $request, string $id): UserResource
    {
        $tenantId = app('tenant_id');
        $user     = $this->userService->update($id, $tenantId, $request->validated());

        return new UserResource($user);
    }

    public function destroy(string $id): JsonResponse
    {
        $tenantId = app('tenant_id');
        $this->userService->delete($id, $tenantId);

        return response()->json(['message' => 'User deleted successfully']);
    }

    public function restore(string $id): JsonResponse
    {
        $this->userService->restore($id);

        return response()->json(['message' => 'User restored successfully']);
    }
}

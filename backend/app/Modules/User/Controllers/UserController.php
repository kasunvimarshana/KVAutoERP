<?php

namespace App\Modules\User\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\User\DTOs\UserDTO;
use App\Modules\User\Requests\CreateUserRequest;
use App\Modules\User\Requests\UpdateUserRequest;
use App\Modules\User\Resources\UserResource;
use App\Modules\User\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    public function __construct(private readonly UserService $userService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['search', 'role', 'sort_by', 'sort_dir', 'tenant_id']);
        $perPage = $request->input('per_page', 15);
        $users = $this->userService->list($filters, $perPage);
        return UserResource::collection($users);
    }

    public function show(int $id): UserResource
    {
        $user = $this->userService->get($id);
        return new UserResource($user);
    }

    public function store(CreateUserRequest $request): JsonResponse
    {
        $dto = UserDTO::fromArray($request->validated());
        $user = $this->userService->create($dto);
        return (new UserResource($user))->response()->setStatusCode(201);
    }

    public function update(UpdateUserRequest $request, int $id): UserResource
    {
        $dto = UserDTO::fromArray($request->validated());
        $user = $this->userService->update($id, $dto);
        return new UserResource($user);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->userService->delete($id);
        return response()->json(['message' => 'User deleted successfully']);
    }
}

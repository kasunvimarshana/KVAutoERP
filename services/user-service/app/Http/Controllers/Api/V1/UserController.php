<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Contracts\AttachmentServiceContract;
use App\Contracts\UserServiceContract;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly UserServiceContract       $userService,
        private readonly AttachmentServiceContract $attachmentService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = (string) $request->attributes->get('tenant_id', '');
        $filters  = $request->only(['status', 'search']);
        $perPage  = (int) $request->get('per_page', 20);

        $result = $this->userService->listUsers($tenantId, $filters, $perPage);

        return $this->paginatedResponse(
            UserResource::collection(collect($result['data']))->resolve(),
            $result['pagination'],
        );
    }

    public function show(string $id): JsonResponse
    {
        $user = $this->userService->findById($id);

        if (! $user) {
            return $this->errorResponse('User not found', [], 404);
        }

        return $this->successResponse($user);
    }

    public function store(CreateUserRequest $request): JsonResponse
    {
        $user = $this->userService->createUser($request->validated());

        return $this->successResponse($user, 'User created successfully', 201);
    }

    public function update(UpdateUserRequest $request, string $id): JsonResponse
    {
        $user = $this->userService->updateUser($id, $request->validated());

        return $this->successResponse($user, 'User updated successfully');
    }

    public function destroy(string $id): JsonResponse
    {
        $this->userService->deleteUser($id);

        return $this->successResponse(null, 'User deleted successfully');
    }

    public function uploadAvatar(Request $request, string $id): JsonResponse
    {
        $request->validate(['avatar' => ['required', 'image', 'max:2048']]);

        $url = $this->attachmentService->uploadAvatar($id, $request->file('avatar'));

        return $this->successResponse(['avatar_url' => $url], 'Avatar uploaded successfully');
    }

    public function profile(Request $request): JsonResponse
    {
        $userId = (string) $request->attributes->get('user_id', '');
        $user   = $this->userService->findById($userId);

        if (! $user) {
            return $this->errorResponse('User not found', [], 404);
        }

        return $this->successResponse($user);
    }
}

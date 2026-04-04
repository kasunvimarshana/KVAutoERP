<?php

declare(strict_types=1);

namespace Modules\User\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\User\Application\Contracts\CreateUserServiceInterface;
use Modules\User\Application\Contracts\DeleteUserServiceInterface;
use Modules\User\Application\Contracts\GetUserServiceInterface;
use Modules\User\Application\Contracts\ListUsersServiceInterface;
use Modules\User\Application\Contracts\UpdateUserServiceInterface;
use Modules\User\Application\DTOs\CreateUserData;
use Modules\User\Application\DTOs\UpdateUserData;
use Modules\User\Infrastructure\Http\Requests\CreateUserRequest;
use Modules\User\Infrastructure\Http\Requests\UpdateUserRequest;
use Modules\User\Infrastructure\Http\Resources\UserResource;

class UserController extends Controller
{
    public function __construct(
        private readonly CreateUserServiceInterface $createService,
        private readonly UpdateUserServiceInterface $updateService,
        private readonly DeleteUserServiceInterface $deleteService,
        private readonly GetUserServiceInterface $getService,
        private readonly ListUsersServiceInterface $listService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $users = $this->listService->execute(
            filters: $request->only(['tenant_id', 'status']),
            perPage: (int) $request->get('per_page', 15),
            page: (int) $request->get('page', 1),
        );

        return response()->json([
            'data' => UserResource::collection($users->items()),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
                'per_page'     => $users->perPage(),
                'total'        => $users->total(),
            ],
        ]);
    }

    public function store(CreateUserRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $data = CreateUserData::fromArray([
            'tenantId' => $validated['tenant_id'],
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => $validated['password'],
            'avatar'   => $validated['avatar'] ?? null,
            'timezone' => $validated['timezone'] ?? 'UTC',
            'locale'   => $validated['locale'] ?? 'en',
            'status'   => $validated['status'] ?? 'active',
        ]);

        $user = $this->createService->execute($data);

        return response()->json(new UserResource($user), 201);
    }

    public function show(int $id): JsonResponse
    {
        $user = $this->getService->execute($id);

        return response()->json(new UserResource($user));
    }

    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        $data = UpdateUserData::fromArray($request->validated());
        $user = $this->updateService->execute($id, $data);

        return response()->json(new UserResource($user));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->deleteService->execute($id);

        return response()->json(null, 204);
    }
}

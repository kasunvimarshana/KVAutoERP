<?php

declare(strict_types=1);

namespace Modules\User\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Core\Infrastructure\Http\Controllers\AuthorizedController;
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

class UserController extends AuthorizedController
{
    public function __construct(
        private readonly CreateUserServiceInterface $createService,
        private readonly UpdateUserServiceInterface $updateService,
        private readonly DeleteUserServiceInterface $deleteService,
        private readonly GetUserServiceInterface $getService,
        private readonly ListUsersServiceInterface $listService,
    ) {}

    public function index(): JsonResponse
    {
        $tenantId = (int) request()->header('X-Tenant-ID', auth()->user()?->tenant_id ?? 0);
        $page = (int) request()->get('page', 1);
        $perPage = (int) request()->get('per_page', 15);
        $result = $this->listService->execute($tenantId, $page, $perPage);

        return response()->json($result);
    }

    public function show(int $id): JsonResponse
    {
        $user = $this->getService->execute($id);

        return (new UserResource($user))->response();
    }

    public function store(CreateUserRequest $request): JsonResponse
    {
        $data = new CreateUserData(
            tenantId: (int) ($request->input('tenant_id') ?? $request->header('X-Tenant-ID')),
            name: $request->input('name'),
            email: $request->input('email'),
            password: $request->input('password'),
            orgUnitId: $request->input('org_unit_id') ? (int) $request->input('org_unit_id') : null,
            phone: $request->input('phone'),
            locale: $request->input('locale', 'en'),
            timezone: $request->input('timezone', 'UTC'),
        );

        $user = $this->createService->execute($data);

        return (new UserResource($user))->response()->setStatusCode(201);
    }

    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        $data = new UpdateUserData(
            name: $request->input('name'),
            email: $request->input('email'),
            phone: $request->input('phone'),
            locale: $request->input('locale'),
            timezone: $request->input('timezone'),
            status: $request->input('status'),
        );

        $user = $this->updateService->execute($id, $data);

        return (new UserResource($user))->response();
    }

    public function destroy(int $id): JsonResponse
    {
        $this->deleteService->execute($id);

        return response()->json(null, 204);
    }
}

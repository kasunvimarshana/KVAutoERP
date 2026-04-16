<?php

declare(strict_types=1);

namespace Modules\User\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Modules\Core\Infrastructure\Http\Controllers\AuthorizedController;
use Modules\User\Application\Contracts\AssignRoleServiceInterface;
use Modules\User\Application\Contracts\CreateUserServiceInterface;
use Modules\User\Application\Contracts\DeleteUserServiceInterface;
use Modules\User\Application\Contracts\FindUserServiceInterface;
use Modules\User\Application\Contracts\UpdatePreferencesServiceInterface;
use Modules\User\Application\Contracts\UpdateUserServiceInterface;
use Modules\User\Application\DTOs\UserData;
use Modules\User\Application\DTOs\UserPreferencesData;
use Modules\User\Domain\Entities\User;
use Modules\User\Infrastructure\Http\Requests\AssignRoleRequest;
use Modules\User\Infrastructure\Http\Requests\ListUserRequest;
use Modules\User\Infrastructure\Http\Requests\StoreUserRequest;
use Modules\User\Infrastructure\Http\Requests\UpdatePreferencesRequest;
use Modules\User\Infrastructure\Http\Requests\UpdateUserRequest;
use Modules\User\Infrastructure\Http\Resources\UserCollection;
use Modules\User\Infrastructure\Http\Resources\UserResource;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserController extends AuthorizedController
{
    public function __construct(
        protected FindUserServiceInterface $findService,
        protected CreateUserServiceInterface $createService,
        protected UpdateUserServiceInterface $updateService,
        protected DeleteUserServiceInterface $deleteService,
        protected AssignRoleServiceInterface $assignRoleService,
        protected UpdatePreferencesServiceInterface $updatePreferencesService
    ) {}

    public function index(ListUserRequest $request): UserCollection
    {
        $this->authorize('viewAny', User::class);
        $validated = $request->validated();

        $filters = array_filter([
            'tenant_id' => $validated['tenant_id'] ?? null,
            'email' => $validated['email'] ?? null,
            'first_name' => $validated['first_name'] ?? null,
            'last_name' => $validated['last_name'] ?? null,
            'status' => $validated['status'] ?? null,
        ], static fn (mixed $value): bool => $value !== null && $value !== '');

        if (array_key_exists('active', $validated)) {
            $filters['status'] = $request->boolean('active') ? 'active' : 'inactive';
        }

        $perPage = (int) ($validated['per_page'] ?? 15);
        $page = (int) ($validated['page'] ?? 1);
        $sort = $validated['sort'] ?? null;
        $include = $validated['include'] ?? null;

        $users = $this->findService->list($filters, $perPage, $page, $sort, $include);

        return new UserCollection($users);
    }


    public function store(StoreUserRequest $request): JsonResponse
    {
        $this->authorize('create', User::class);
        $dto = UserData::fromArray($request->validated());
        $user = $this->createService->execute($dto->toArray());

        return (new UserResource($user))->response()->setStatusCode(201);
    }

    
    public function show(int $id): UserResource
    {
        $user = $this->findUserOrFail($id);
        $this->authorize('view', $user);

        return new UserResource($user);
    }

    
    public function update(UpdateUserRequest $request, int $id): UserResource
    {
        $user = $this->findUserOrFail($id);
        $this->authorize('update', $user);

        $payload = $request->validated();
        $payload['id'] = $id;
        $updated = $this->updateService->execute($payload);

        return new UserResource($updated);
    }

    
    public function destroy(int $id): JsonResponse
    {
        $user = $this->findUserOrFail($id);
        $this->authorize('delete', $user);
        $this->deleteService->execute(['id' => $id]);

        return Response::json(['message' => 'User deleted successfully']);
    }


    public function assignRole(AssignRoleRequest $request, int $id): JsonResponse
    {
        $user = $this->findUserOrFail($id);
        $this->authorize('assignRole', $user);
        $validated = $request->validated();
        $this->assignRoleService->execute(['user_id' => $id, 'role_id' => $validated['role_id']]);

        return Response::json(['message' => 'Role assigned successfully']);
    }


    public function updatePreferences(UpdatePreferencesRequest $request, int $id): UserResource
    {
        $user = $this->findUserOrFail($id);
        $this->authorize('updatePreferences', $user);
        $validated = $request->validated();
        $dto = UserPreferencesData::fromArray($validated);
        $updated = $this->updatePreferencesService->execute(['user_id' => $id] + $dto->toArray());

        return new UserResource($updated);
    }

    private function findUserOrFail(int $userId): User
    {
        $user = $this->findService->find($userId);
        if (! $user) {
            throw new NotFoundHttpException('User not found.');
        }

        return $user;
    }


}

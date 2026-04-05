<?php

declare(strict_types=1);

namespace Modules\Auth\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Auth\Application\Contracts\UserServiceInterface;
use Modules\Auth\Domain\Entities\User;
use Modules\Core\Domain\Exceptions\DomainException;
use Modules\Core\Domain\Exceptions\NotFoundException;

class UserController extends Controller
{
    public function __construct(
        private readonly UserServiceInterface $userService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? $request->user()?->tenant_id;
        $users = $this->userService->getAllByTenant($tenantId);

        return response()->json($users->map(fn (User $u) => $this->serialize($u))->values());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id'  => 'required|uuid',
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|max:255',
            'password'   => 'required|string|min:8',
            'role_id'    => 'nullable|uuid',
            'status'     => 'nullable|in:active,inactive',
            'preferences'=> 'nullable|array',
        ]);

        try {
            $user = $this->userService->createUser($validated);

            return response()->json($this->serialize($user), 201);
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            return response()->json($this->serialize($this->userService->getUser($id)));
        } catch (NotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'name'       => 'sometimes|string|max:255',
            'email'      => 'sometimes|email|max:255',
            'password'   => 'sometimes|string|min:8',
            'role_id'    => 'nullable|uuid',
            'status'     => 'nullable|in:active,inactive',
            'preferences'=> 'nullable|array',
        ]);

        try {
            $user = $this->userService->updateUser($id, $validated);

            return response()->json($this->serialize($user));
        } catch (NotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $this->userService->deleteUser($id);

            return response()->json(null, 204);
        } catch (NotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    private function serialize(User $user): array
    {
        return [
            'id'          => $user->getId(),
            'tenant_id'   => $user->getTenantId(),
            'name'        => $user->getName(),
            'email'       => $user->getEmail(),
            'role'        => $user->getRole(),
            'status'      => $user->getStatus(),
            'preferences' => $user->getPreferences(),
            'created_at'  => $user->getCreatedAt()?->format('Y-m-d H:i:s'),
        ];
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Repositories\UserRepository;
use Shared\Core\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends BaseController
{
    /**
     * @var UserRepository
     */
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Display a listing of users
     */
    public function index(Request $request): JsonResponse
    {
        $users = $this->userRepository
            ->with(['roles', 'permissions'])
            ->paginate($request->get('per_page', 15));

        return $this->success(UserResource::collection($users)->response()->getData(true));
    }

    /**
     * Display the specified user
     */
    public function show($id): JsonResponse
    {
        $user = $this->userRepository->with(['roles', 'permissions'])->find($id);

        if (!$user) {
            return $this->error('User not found', 404);
        }

        return $this->success(new UserResource($user));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, $id): JsonResponse
    {
        $updated = $this->userRepository->update($request->all(), $id);

        if (!$updated) {
            return $this->error('User not found or update failed', 404);
        }

        return $this->success(null, 'User updated successfully');
    }

    /**
     * Remove the specified user
     */
    public function destroy($id): JsonResponse
    {
        $deleted = $this->userRepository->delete($id);

        if (!$deleted) {
            return $this->error('User not found or delete failed', 404);
        }

        return $this->success(null, 'User deleted successfully');
    }
}

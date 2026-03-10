<?php
namespace App\Http\Controllers;
use App\Exceptions\ServiceException;
use App\Services\UserService;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserProfileResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private readonly UserService $userService) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id', $request->header('X-Tenant-ID'));
        $users    = $this->userService->list($tenantId, $request->all());
        return response()->json(['success' => true, 'data' => UserProfileResource::collection($users instanceof \Illuminate\Pagination\AbstractPaginator ? $users->getCollection() : $users), 'meta' => $users instanceof \Illuminate\Pagination\AbstractPaginator ? ['total' => $users->total(), 'current_page' => $users->currentPage(), 'per_page' => $users->perPage(), 'last_page' => $users->lastPage()] : null]);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            $tenantId = $request->attributes->get('tenant_id', $request->header('X-Tenant-ID'));
            $user     = $this->userService->create($tenantId, $request->validated());
            return response()->json(new UserProfileResource($user), 201);
        } catch (ServiceException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], $e->getHttpStatus());
        }
    }

    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $tenantId = $request->attributes->get('tenant_id', $request->header('X-Tenant-ID'));
            $user     = $this->userService->get($id, $tenantId);
            return response()->json(new UserProfileResource($user));
        } catch (ServiceException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], $e->getHttpStatus());
        }
    }

    public function update(UpdateUserRequest $request, string $id): JsonResponse
    {
        try {
            $tenantId = $request->attributes->get('tenant_id', $request->header('X-Tenant-ID'));
            $user     = $this->userService->update($id, $tenantId, $request->validated());
            return response()->json(new UserProfileResource($user));
        } catch (ServiceException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], $e->getHttpStatus());
        }
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $tenantId = $request->attributes->get('tenant_id', $request->header('X-Tenant-ID'));
            $this->userService->delete($id, $tenantId);
            return response()->json(['success' => true, 'message' => 'User profile deleted.']);
        } catch (ServiceException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], $e->getHttpStatus());
        }
    }

    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $request->validate(['status' => 'required|string|in:active,inactive,suspended']);
        try {
            $tenantId = $request->attributes->get('tenant_id', $request->header('X-Tenant-ID'));
            $user     = $this->userService->updateStatus($id, $tenantId, $request->input('status'));
            return response()->json(new UserProfileResource($user));
        } catch (ServiceException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], $e->getHttpStatus());
        }
    }
}

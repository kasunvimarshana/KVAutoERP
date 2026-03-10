<?php
namespace App\Http\Controllers;
use App\Exceptions\ServiceException;
use App\Services\CategoryService;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryService $categoryService) {}

    private function tenantId(Request $request): string
    {
        return $request->attributes->get('tenant_id', $request->header('X-Tenant-ID', ''));
    }

    public function index(Request $request): JsonResponse
    {
        $cats = $this->categoryService->list($this->tenantId($request), $request->all());
        $isPaginated = $cats instanceof \Illuminate\Pagination\AbstractPaginator;
        return response()->json(['success' => true, 'data' => CategoryResource::collection($isPaginated ? $cats->getCollection() : $cats)]);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        try {
            return response()->json(new CategoryResource($this->categoryService->create($this->tenantId($request), $request->validated())), 201);
        } catch (ServiceException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function show(Request $request, string $id): JsonResponse
    {
        try {
            return response()->json(new CategoryResource($this->categoryService->get($id, $this->tenantId($request))));
        } catch (ServiceException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], $e->getHttpStatus());
        }
    }

    public function update(UpdateCategoryRequest $request, string $id): JsonResponse
    {
        try {
            return response()->json(new CategoryResource($this->categoryService->update($id, $this->tenantId($request), $request->validated())));
        } catch (ServiceException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], $e->getHttpStatus());
        }
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $this->categoryService->delete($id, $this->tenantId($request));
            return response()->json(['success' => true, 'message' => 'Category deleted.']);
        } catch (ServiceException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], $e->getHttpStatus());
        }
    }
}

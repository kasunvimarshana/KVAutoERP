<?php

namespace Modules\Core\Infrastructure\Http\Controllers;

use Modules\Core\Application\Services\BaseService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

abstract class BaseController extends Controller
{
    protected BaseService $service;
    protected string $resourceClass;
    protected string $dtoClass;

    public function __construct(BaseService $service, string $resourceClass, string $dtoClass)
    {
        $this->service = $service;
        $this->resourceClass = $resourceClass;
        $this->dtoClass = $dtoClass;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', $this->getModelClass());
        $filters = $request->except(['page', 'per_page', 'sort', 'include']);
        $perPage = $request->input('per_page', config('core.pagination.per_page', 15));
        $page = $request->input('page', 1);
        $sort = $request->input('sort');
        $include = $request->input('include');

        $results = $this->service->list($filters, $perPage, $page, $sort, $include);
        return $this->resourceClass::collection($results);
    }

    public function store(Request $request)
    {
        $this->authorize('create', $this->getModelClass());
        $validated = $request->validate($this->rules());
        $dto = $this->dtoClass::fromArray($validated);
        $model = $this->service->execute($dto->toArray());
        return new $this->resourceClass($model);
    }

    public function show($id)
    {
        $model = $this->service->find($id);
        if (!$model) {
            abort(404);
        }
        $this->authorize('view', $model);
        return new $this->resourceClass($model);
    }

    public function update(Request $request, $id)
    {
        $model = $this->service->find($id);
        if (!$model) {
            abort(404);
        }
        $this->authorize('update', $model);
        $validated = $request->validate($this->rules());
        $dto = $this->dtoClass::fromArray($validated);
        $updated = $this->service->update($id, $dto->toArray());
        return new $this->resourceClass($updated);
    }

    public function destroy($id)
    {
        $model = $this->service->find($id);
        if (!$model) {
            abort(404);
        }
        $this->authorize('delete', $model);
        $this->service->delete($id);
        return response()->json(['message' => 'Resource deleted successfully']);
    }

    abstract protected function getModelClass(): string;
    protected function rules(): array
    {
        return [];
    }
}

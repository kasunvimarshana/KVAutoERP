<?php
declare(strict_types=1);
namespace Modules\Category\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Category\Application\Contracts\CreateCategoryServiceInterface;
use Modules\Category\Application\Contracts\DeleteCategoryServiceInterface;
use Modules\Category\Application\Contracts\UpdateCategoryServiceInterface;
use Modules\Category\Infrastructure\Http\Requests\StoreCategoryRequest;
use Modules\Category\Infrastructure\Http\Requests\UpdateCategoryRequest;
use Modules\Core\Infrastructure\Http\Controllers\AuthorizedController;

class CategoryController extends AuthorizedController
{
    public function __construct(
        private CreateCategoryServiceInterface $createService,
        private UpdateCategoryServiceInterface $updateService,
        private DeleteCategoryServiceInterface $deleteService,
    ) {}

    public function index(): JsonResponse { return response()->json([]); }
    public function show(int $id): JsonResponse { return response()->json([]); }
    public function store(StoreCategoryRequest $request): JsonResponse { return response()->json([]); }
    public function update(UpdateCategoryRequest $request, int $id): JsonResponse { return response()->json([]); }
    public function destroy(int $id): JsonResponse { return response()->json([]); }
}

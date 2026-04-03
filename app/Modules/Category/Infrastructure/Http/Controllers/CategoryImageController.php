<?php
declare(strict_types=1);
namespace Modules\Category\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Category\Application\Contracts\DeleteCategoryImageServiceInterface;
use Modules\Category\Application\Contracts\UploadCategoryImageServiceInterface;
use Modules\Category\Infrastructure\Http\Requests\UploadCategoryImageRequest;
use Modules\Core\Infrastructure\Http\Controllers\AuthorizedController;

class CategoryImageController extends AuthorizedController
{
    public function __construct(
        private UploadCategoryImageServiceInterface $uploadService,
        private DeleteCategoryImageServiceInterface $deleteService,
    ) {}

    public function store(UploadCategoryImageRequest $request, int $categoryId): JsonResponse { return response()->json([]); }
    public function destroy(int $categoryId, int $imageId): JsonResponse { return response()->json([]); }
}

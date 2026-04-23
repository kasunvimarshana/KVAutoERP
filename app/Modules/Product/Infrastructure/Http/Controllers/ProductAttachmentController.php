<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Core\Infrastructure\Http\Controllers\AuthorizedController;
use Modules\Product\Application\Contracts\CreateProductAttachmentServiceInterface;
use Modules\Product\Application\Contracts\DeleteProductAttachmentServiceInterface;
use Modules\Product\Application\Contracts\FindProductAttachmentServiceInterface;
use Modules\Product\Application\Contracts\UpdateProductAttachmentServiceInterface;
use Modules\Product\Domain\Entities\ProductAttachment;
use Modules\Product\Infrastructure\Http\Requests\ListProductAttachmentRequest;
use Modules\Product\Infrastructure\Http\Requests\StoreProductAttachmentRequest;
use Modules\Product\Infrastructure\Http\Requests\UpdateProductAttachmentRequest;
use Modules\Product\Infrastructure\Http\Resources\ProductAttachmentCollection;
use Modules\Product\Infrastructure\Http\Resources\ProductAttachmentResource;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductAttachmentController extends AuthorizedController
{
    public function __construct(
        protected CreateProductAttachmentServiceInterface $createProductAttachmentService,
        protected UpdateProductAttachmentServiceInterface $updateProductAttachmentService,
        protected DeleteProductAttachmentServiceInterface $deleteProductAttachmentService,
        protected FindProductAttachmentServiceInterface $findProductAttachmentService,
    ) {}

    public function index(ListProductAttachmentRequest $request): JsonResponse
    {
        $this->authorize('viewAny', ProductAttachment::class);
        $validated = $request->validated();

        $items = $this->findProductAttachmentService->list(
            filters: [],
            perPage: (int) ($validated['per_page'] ?? 15),
            page: (int) ($validated['page'] ?? 1),
        );

        return (new ProductAttachmentCollection($items))->response();
    }

    public function store(StoreProductAttachmentRequest $request): JsonResponse
    {
        $this->authorize('create', ProductAttachment::class);

        $item = $this->createProductAttachmentService->execute($request->validated());

        return (new ProductAttachmentResource($item))
            ->response()
            ->setStatusCode(HttpResponse::HTTP_CREATED);
    }

    public function show(int $productAttachment): ProductAttachmentResource
    {
        $item = $this->findOrFail($productAttachment);
        $this->authorize('view', $item);

        return new ProductAttachmentResource($item);
    }

    public function update(UpdateProductAttachmentRequest $request, int $productAttachment): ProductAttachmentResource
    {
        $item = $this->findOrFail($productAttachment);
        $this->authorize('update', $item);

        $payload = $request->validated();
        $payload['id'] = $productAttachment;

        $updated = $this->updateProductAttachmentService->execute($payload);

        return new ProductAttachmentResource($updated);
    }

    public function destroy(int $productAttachment): JsonResponse
    {
        $item = $this->findOrFail($productAttachment);
        $this->authorize('delete', $item);

        $this->deleteProductAttachmentService->execute(['id' => $productAttachment]);

        return response()->json(['message' => 'ProductAttachment deleted successfully']);
    }

    private function findOrFail(int $id): ProductAttachment
    {
        $item = $this->findProductAttachmentService->find($id);

        if (! $item) {
            throw new NotFoundHttpException('ProductAttachment not found.');
        }

        return $item;
    }
}

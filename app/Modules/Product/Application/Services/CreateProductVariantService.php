<?php
namespace Modules\Product\Application\Services;

use Illuminate\Support\Facades\Event;
use Modules\Product\Application\Contracts\CreateProductVariantServiceInterface;
use Modules\Product\Application\DTOs\ProductVariantData;
use Modules\Product\Domain\Entities\ProductVariant;
use Modules\Product\Domain\Events\ProductVariantCreated;
use Modules\Product\Domain\RepositoryInterfaces\ProductRepositoryInterface;
use Modules\Product\Domain\RepositoryInterfaces\ProductVariantRepositoryInterface;

class CreateProductVariantService implements CreateProductVariantServiceInterface
{
    public function __construct(
        private readonly ProductVariantRepositoryInterface $variantRepository,
        private readonly ProductRepositoryInterface $productRepository,
    ) {}

    public function execute(ProductVariantData $data): ProductVariant
    {
        $variant = $this->variantRepository->create($data->toArray());
        $product = $this->productRepository->findById($variant->productId);
        $tenantId = $product?->tenantId ?? 0;
        Event::dispatch(new ProductVariantCreated($tenantId, $variant->id));
        return $variant;
    }
}

<?php declare(strict_types=1);
namespace Modules\Product\Application\Services;
use Illuminate\Support\Collection;
use Modules\Product\Application\Contracts\FindProductImagesServiceInterface;
use Modules\Product\Domain\Entities\ProductImage;
use Modules\Product\Domain\RepositoryInterfaces\ProductImageRepositoryInterface;
class FindProductImagesService implements FindProductImagesServiceInterface {
    public function __construct(private ProductImageRepositoryInterface $repository) {}
    public function findByProduct(int $productId): Collection { return $this->repository->getByProduct($productId); }
    public function findByUuid(string $uuid): ?ProductImage { return $this->repository->findByUuid($uuid); }
}

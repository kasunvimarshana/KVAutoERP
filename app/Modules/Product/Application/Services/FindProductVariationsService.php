<?php declare(strict_types=1);
namespace Modules\Product\Application\Services;
use Illuminate\Support\Collection;
use Modules\Product\Application\Contracts\FindProductVariationsServiceInterface;
use Modules\Product\Domain\RepositoryInterfaces\ProductVariationRepositoryInterface;
class FindProductVariationsService implements FindProductVariationsServiceInterface {
    public function __construct(private ProductVariationRepositoryInterface $repository) {}
    public function find(mixed $id): mixed { return $this->repository->find($id); }
    public function findByProduct(int $productId): Collection { return $this->repository->findByProduct($productId); }
}

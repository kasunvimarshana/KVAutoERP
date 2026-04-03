<?php declare(strict_types=1);
namespace Modules\Product\Application\Services;
use Illuminate\Support\Collection;
use Modules\Product\Application\Contracts\FindComboItemsServiceInterface;
use Modules\Product\Domain\RepositoryInterfaces\ComboItemRepositoryInterface;
class FindComboItemsService implements FindComboItemsServiceInterface {
    public function __construct(private ComboItemRepositoryInterface $repository) {}
    public function find(mixed $id): mixed { return $this->repository->find($id); }
    public function findByProduct(int $productId): Collection { return $this->repository->findByProduct($productId); }
}

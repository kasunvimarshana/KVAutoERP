<?php
declare(strict_types=1);
namespace Modules\Product\Application\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Product\Application\Contracts\FindProductServiceInterface;
use Modules\Product\Domain\RepositoryInterfaces\ProductRepositoryInterface;

class FindProductService implements FindProductServiceInterface
{
    public function __construct(private ProductRepositoryInterface $repository) {}

    public function find(mixed $id): mixed { return $this->repository->find($id); }

    public function list(array $filters = [], ?int $perPage = null, int $page = 1, ?string $sort = null, ?string $include = null): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage, ['*'], 'page', $page);
    }

    protected function handle(array $data): mixed
    {
        throw new \BadMethodCallException('FindProductService does not support write operations.');
    }
}

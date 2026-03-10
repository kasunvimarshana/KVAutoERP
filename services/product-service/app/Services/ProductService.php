<?php
namespace App\Services;
use App\Exceptions\ServiceException;
use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class ProductService
{
    public function __construct(
        private readonly ProductRepositoryInterface $repository,
    ) {}

    public function list(string $tenantId, array $params = []): LengthAwarePaginator|Collection
    {
        $filters = ['tenant_id' => $tenantId];
        if (!empty($params['category_id'])) $filters['category_id'] = $params['category_id'];
        if (!empty($params['status']))      $filters['status']      = $params['status'];
        return $this->repository->all($filters, $params);
    }

    public function create(string $tenantId, array $data): Product
    {
        $data['tenant_id'] = $tenantId;
        if (!empty($data['code']) && $this->repository->findByCode($data['code'], $tenantId)) {
            throw new ServiceException("Product code \"{$data['code']}\" already exists for this tenant.", 422);
        }
        $product = $this->repository->create($data);
        Log::info('Product created', ['id' => $product->id, 'tenant_id' => $tenantId]);
        return $product;
    }

    public function get(string $id, string $tenantId): Product
    {
        $product = $this->repository->findById($id);
        if (!$product || $product->tenant_id !== $tenantId) {
            throw new ServiceException('Product not found.', 404);
        }
        return $product->load('category');
    }

    public function update(string $id, string $tenantId, array $data): Product
    {
        $product = $this->get($id, $tenantId);
        return $this->repository->update($product->id, $data)->load('category');
    }

    public function delete(string $id, string $tenantId): void
    {
        $product = $this->get($id, $tenantId);
        $this->repository->delete($product->id);
    }

    /**
     * Cross-service lookup - returns product data for Inventory/Order services.
     * Supports lookup by IDs or product codes.
     */
    public function lookup(string $tenantId, array $ids = [], array $codes = []): Collection
    {
        if (!empty($ids)) {
            return $this->repository->findByIds($ids, $tenantId);
        }
        if (!empty($codes)) {
            return $this->repository->findByCodes($codes, $tenantId);
        }
        return new Collection();
    }
}

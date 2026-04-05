<?php declare(strict_types=1);
namespace Modules\Product\Application\Services;
use Modules\Product\Application\Contracts\ProductServiceInterface;
use Modules\Product\Domain\Entities\Product;
use Modules\Product\Domain\RepositoryInterfaces\ProductRepositoryInterface;
use Modules\Core\Domain\Exceptions\NotFoundException;
class ProductService implements ProductServiceInterface {
    public function __construct(private readonly ProductRepositoryInterface $repo) {}
    public function create(array $data): Product {
        $product = new Product(
            null,
            $data['tenant_id'],
            $data['sku'],
            $data['name'],
            $data['type'] ?? 'physical',
            $data['category_id'] ?? null,
            (float)($data['cost_price'] ?? 0.0),
            (float)($data['sale_price'] ?? 0.0),
            $data['currency'] ?? 'USD',
            $data['description'] ?? null,
            true,
            $data['is_taxable'] ?? false,
            $data['tax_group_id'] ?? null,
            $data['barcode'] ?? null,
            $data['unit'] ?? null,
        );
        return $this->repo->save($product);
    }
    public function findById(int $id): ?Product { return $this->repo->findById($id); }
    public function update(int $id, array $data): Product {
        $p = $this->repo->findById($id);
        if (!$p) throw new NotFoundException('Product', $id);
        $updated = new Product(
            $p->getId(),
            $p->getTenantId(),
            $data['sku'] ?? $p->getSku(),
            $data['name'] ?? $p->getName(),
            $data['type'] ?? $p->getType(),
            $data['category_id'] ?? $p->getCategoryId(),
            (float)($data['cost_price'] ?? $p->getCostPrice()),
            (float)($data['sale_price'] ?? $p->getSalePrice()),
            $data['currency'] ?? $p->getCurrency(),
            $data['description'] ?? $p->getDescription(),
            $data['is_active'] ?? $p->isActive(),
            $data['is_taxable'] ?? $p->isTaxable(),
            $data['tax_group_id'] ?? $p->getTaxGroupId(),
            $data['barcode'] ?? $p->getBarcode(),
            $data['unit'] ?? $p->getUnit(),
        );
        return $this->repo->save($updated);
    }
    public function delete(int $id): void { $this->repo->delete($id); }
}

<?php
declare(strict_types=1);
namespace Modules\Product\Application\Services;

use Modules\Core\Application\Services\BaseService;
use Modules\Core\Domain\ValueObjects\Money;
use Modules\Core\Domain\ValueObjects\Sku;
use Modules\Product\Application\Contracts\CreateProductServiceInterface;
use Modules\Product\Domain\Entities\Product;
use Modules\Product\Domain\RepositoryInterfaces\ProductRepositoryInterface;
use Modules\Product\Domain\ValueObjects\ProductAttribute;
use Modules\Product\Domain\ValueObjects\UnitOfMeasure;

class CreateProductService extends BaseService implements CreateProductServiceInterface
{
    public function __construct(ProductRepositoryInterface $repository) { parent::__construct($repository); }

    protected function handle(array $data): Product
    {
        $sku   = new Sku($data['sku']);
        $price = new Money($data['price'], $data['currency'] ?? 'USD');

        $unitsOfMeasure = [];
        if (!empty($data['units_of_measure'])) {
            foreach ($data['units_of_measure'] as $uom) {
                $unitsOfMeasure[] = new UnitOfMeasure($uom['unit'], $uom['type'], $uom['conversion_factor']);
            }
        }

        $productAttributes = [];
        if (!empty($data['product_attributes'])) {
            foreach ($data['product_attributes'] as $attr) {
                $productAttributes[] = new ProductAttribute($attr['code'], $attr['name'], $attr['allowed_values'] ?? []);
            }
        }

        $product = new Product(
            tenantId:          $data['tenant_id'],
            sku:               $sku,
            name:              $data['name'],
            price:             $price,
            description:       $data['description'] ?? null,
            category:          $data['category'] ?? null,
            status:            $data['status'] ?? 'active',
            type:              $data['type'] ?? 'physical',
            unitsOfMeasure:    $unitsOfMeasure,
            productAttributes: $productAttributes,
            attributes:        $data['attributes'] ?? null,
            metadata:          $data['metadata'] ?? null,
        );

        return $this->repository->save($product);
    }
}

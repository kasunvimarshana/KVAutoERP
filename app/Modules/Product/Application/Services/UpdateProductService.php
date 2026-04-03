<?php declare(strict_types=1);
namespace Modules\Product\Application\Services;
use Modules\Core\Application\Services\BaseService;
use Modules\Core\Domain\ValueObjects\Money;
use Modules\Product\Application\Contracts\UpdateProductServiceInterface;
use Modules\Product\Domain\Exceptions\ProductNotFoundException;
use Modules\Product\Domain\RepositoryInterfaces\ProductRepositoryInterface;
use Modules\Product\Domain\ValueObjects\ProductAttribute;
use Modules\Product\Domain\ValueObjects\UnitOfMeasure;
class UpdateProductService extends BaseService implements UpdateProductServiceInterface {
    public function __construct(ProductRepositoryInterface $repository) { parent::__construct($repository); }
    protected function handle(array $data): mixed {
        $product = $this->repository->find($data['id']);
        if (!$product) throw new ProductNotFoundException($data['id']);
        $price = new Money($data['price'], $data['currency'] ?? 'USD');
        $unitsOfMeasure = null;
        if (isset($data['units_of_measure'])) {
            $unitsOfMeasure = [];
            foreach ($data['units_of_measure'] as $uom) $unitsOfMeasure[] = new UnitOfMeasure($uom['unit'], $uom['type'], $uom['conversion_factor']);
        }
        $productAttributes = null;
        if (array_key_exists('product_attributes', $data)) {
            $productAttributes = [];
            foreach ($data['product_attributes'] as $attr) $productAttributes[] = new ProductAttribute($attr['code'], $attr['name'], $attr['allowed_values'] ?? []);
        }
        $product->updateDetails(name: $data['name'], price: $price, description: $data['description'] ?? null, category: $data['category'] ?? null, attributes: $data['attributes'] ?? null, metadata: $data['metadata'] ?? null, type: $data['type'] ?? null, unitsOfMeasure: $unitsOfMeasure, productAttributes: $productAttributes);
        // Handle explicit status
        if (isset($data['status'])) {
            if ($data['status'] === 'active') $product->activate();
            elseif ($data['status'] === 'inactive') $product->deactivate();
            elseif ($data['status'] === 'draft') $product->draft();
        }
        return $this->repository->save($product);
    }
}

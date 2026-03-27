<?php

namespace Tests\Unit;

use Modules\Product\Application\Contracts\CreateProductServiceInterface;
use Modules\Product\Application\Contracts\DeleteProductImageServiceInterface;
use Modules\Product\Application\Contracts\DeleteProductServiceInterface;
use Modules\Product\Application\Contracts\UpdateProductServiceInterface;
use Modules\Product\Application\Contracts\UploadProductImageServiceInterface;
use Modules\Product\Application\DTOs\ProductData;
use Modules\Product\Application\DTOs\ProductImageData;
use Modules\Product\Application\Services\CreateProductService;
use Modules\Product\Application\Services\DeleteProductImageService;
use Modules\Product\Application\Services\DeleteProductService;
use Modules\Product\Application\Services\UpdateProductService;
use Modules\Product\Application\Services\UploadProductImageService;
use Modules\Product\Application\UseCases\CreateProduct;
use Modules\Product\Application\UseCases\DeleteProduct;
use Modules\Product\Application\UseCases\GetProduct;
use Modules\Product\Application\UseCases\ListProducts;
use Modules\Product\Application\UseCases\UpdateProduct;
use Modules\Product\Domain\Entities\Product;
use Modules\Product\Domain\Entities\ProductImage;
use Modules\Product\Domain\Events\ProductCreated;
use Modules\Product\Domain\Events\ProductDeleted;
use Modules\Product\Domain\Events\ProductUpdated;
use Modules\Product\Domain\Exceptions\ProductImageNotFoundException;
use Modules\Product\Domain\Exceptions\ProductNotFoundException;
use Modules\Product\Domain\RepositoryInterfaces\ProductImageRepositoryInterface;
use Modules\Product\Domain\RepositoryInterfaces\ProductRepositoryInterface;
use Modules\Product\Domain\ValueObjects\ProductType;
use Modules\Product\Domain\ValueObjects\UnitOfMeasure;
use Modules\Product\Infrastructure\Http\Controllers\ProductController;
use Modules\Product\Infrastructure\Http\Controllers\ProductImageController;
use Modules\Product\Infrastructure\Http\Requests\StoreProductRequest;
use Modules\Product\Infrastructure\Http\Requests\UpdateProductRequest;
use Modules\Product\Infrastructure\Http\Requests\UploadProductImageRequest;
use Modules\Product\Infrastructure\Http\Resources\ProductCollection;
use Modules\Product\Infrastructure\Http\Resources\ProductImageResource;
use Modules\Product\Infrastructure\Http\Resources\ProductResource;
use Modules\Product\Infrastructure\Persistence\Eloquent\Models\ProductImageModel;
use Modules\Product\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use Modules\Product\Infrastructure\Persistence\Eloquent\Repositories\EloquentProductImageRepository;
use Modules\Product\Infrastructure\Persistence\Eloquent\Repositories\EloquentProductRepository;
use Modules\Product\Infrastructure\Providers\ProductServiceProvider;
use PHPUnit\Framework\TestCase;

class ProductModuleTest extends TestCase
{
    // ── Domain Entities ───────────────────────────────────────────────────────

    public function test_product_entity_class_exists(): void
    {
        $this->assertTrue(class_exists(Product::class));
    }

    public function test_product_image_entity_class_exists(): void
    {
        $this->assertTrue(class_exists(ProductImage::class));
    }

    // ── Domain Events ─────────────────────────────────────────────────────────

    public function test_all_product_event_classes_exist(): void
    {
        $this->assertTrue(class_exists(ProductCreated::class));
        $this->assertTrue(class_exists(ProductUpdated::class));
        $this->assertTrue(class_exists(ProductDeleted::class));
    }

    // ── Domain Exceptions ─────────────────────────────────────────────────────

    public function test_product_exception_classes_exist(): void
    {
        $this->assertTrue(class_exists(ProductNotFoundException::class));
        $this->assertTrue(class_exists(ProductImageNotFoundException::class));
    }

    // ── Domain Repository Interfaces ─────────────────────────────────────────

    public function test_product_repository_interfaces_exist(): void
    {
        $this->assertTrue(interface_exists(ProductRepositoryInterface::class));
        $this->assertTrue(interface_exists(ProductImageRepositoryInterface::class));
    }

    // ── Application DTOs ─────────────────────────────────────────────────────

    public function test_product_dto_classes_exist(): void
    {
        $this->assertTrue(class_exists(ProductData::class));
        $this->assertTrue(class_exists(ProductImageData::class));
    }

    // ── Application Service Contracts ─────────────────────────────────────────

    public function test_all_product_service_interfaces_exist(): void
    {
        $this->assertTrue(interface_exists(CreateProductServiceInterface::class));
        $this->assertTrue(interface_exists(UpdateProductServiceInterface::class));
        $this->assertTrue(interface_exists(DeleteProductServiceInterface::class));
        $this->assertTrue(interface_exists(UploadProductImageServiceInterface::class));
        $this->assertTrue(interface_exists(DeleteProductImageServiceInterface::class));
    }

    // ── Application Services ──────────────────────────────────────────────────

    public function test_all_product_service_implementations_exist(): void
    {
        $this->assertTrue(class_exists(CreateProductService::class));
        $this->assertTrue(class_exists(UpdateProductService::class));
        $this->assertTrue(class_exists(DeleteProductService::class));
        $this->assertTrue(class_exists(UploadProductImageService::class));
        $this->assertTrue(class_exists(DeleteProductImageService::class));
    }

    public function test_product_service_implementations_implement_their_interfaces(): void
    {
        $this->assertTrue(
            is_subclass_of(CreateProductService::class, CreateProductServiceInterface::class),
            'CreateProductService must implement CreateProductServiceInterface.'
        );
        $this->assertTrue(
            is_subclass_of(UpdateProductService::class, UpdateProductServiceInterface::class),
            'UpdateProductService must implement UpdateProductServiceInterface.'
        );
        $this->assertTrue(
            is_subclass_of(DeleteProductService::class, DeleteProductServiceInterface::class),
            'DeleteProductService must implement DeleteProductServiceInterface.'
        );
        $this->assertTrue(
            is_subclass_of(UploadProductImageService::class, UploadProductImageServiceInterface::class),
            'UploadProductImageService must implement UploadProductImageServiceInterface.'
        );
        $this->assertTrue(
            is_subclass_of(DeleteProductImageService::class, DeleteProductImageServiceInterface::class),
            'DeleteProductImageService must implement DeleteProductImageServiceInterface.'
        );
    }

    // ── Application Use Cases ─────────────────────────────────────────────────

    public function test_all_product_use_case_classes_exist(): void
    {
        $this->assertTrue(class_exists(CreateProduct::class));
        $this->assertTrue(class_exists(UpdateProduct::class));
        $this->assertTrue(class_exists(DeleteProduct::class));
        $this->assertTrue(class_exists(GetProduct::class));
        $this->assertTrue(class_exists(ListProducts::class));
    }

    // ── Infrastructure – Models ───────────────────────────────────────────────

    public function test_product_eloquent_model_classes_exist(): void
    {
        $this->assertTrue(class_exists(ProductModel::class));
        $this->assertTrue(class_exists(ProductImageModel::class));
    }

    // ── Infrastructure – Repositories ─────────────────────────────────────────

    public function test_product_eloquent_repositories_exist(): void
    {
        $this->assertTrue(class_exists(EloquentProductRepository::class));
        $this->assertTrue(class_exists(EloquentProductImageRepository::class));
    }

    public function test_product_eloquent_repositories_implement_interfaces(): void
    {
        $this->assertTrue(
            is_subclass_of(EloquentProductRepository::class, ProductRepositoryInterface::class),
            'EloquentProductRepository must implement ProductRepositoryInterface.'
        );
        $this->assertTrue(
            is_subclass_of(EloquentProductImageRepository::class, ProductImageRepositoryInterface::class),
            'EloquentProductImageRepository must implement ProductImageRepositoryInterface.'
        );
    }

    // ── Infrastructure – HTTP ─────────────────────────────────────────────────

    public function test_product_controller_classes_exist(): void
    {
        $this->assertTrue(class_exists(ProductController::class));
        $this->assertTrue(class_exists(ProductImageController::class));
    }

    public function test_product_form_request_classes_exist(): void
    {
        $this->assertTrue(class_exists(StoreProductRequest::class));
        $this->assertTrue(class_exists(UpdateProductRequest::class));
        $this->assertTrue(class_exists(UploadProductImageRequest::class));
    }

    public function test_product_resource_classes_exist(): void
    {
        $this->assertTrue(class_exists(ProductResource::class));
        $this->assertTrue(class_exists(ProductCollection::class));
        $this->assertTrue(class_exists(ProductImageResource::class));
    }

    // ── Infrastructure – Provider ─────────────────────────────────────────────

    public function test_product_service_provider_class_exists(): void
    {
        $this->assertTrue(class_exists(ProductServiceProvider::class));
    }

    // ── Domain Behaviour ─────────────────────────────────────────────────────

    public function test_product_entity_can_be_constructed(): void
    {
        $sku = new \Modules\Core\Domain\ValueObjects\Sku('PROD-001');
        $price = new \Modules\Core\Domain\ValueObjects\Money(29.99, 'USD');

        $product = new Product(
            tenantId: 1,
            sku: $sku,
            name: 'Widget Pro',
            price: $price,
            description: 'A great widget',
            category: 'Widgets',
            status: 'active',
        );

        $this->assertNull($product->getId());
        $this->assertSame(1, $product->getTenantId());
        $this->assertSame('PROD-001', $product->getSku()->value());
        $this->assertSame('Widget Pro', $product->getName());
        $this->assertSame(29.99, $product->getPrice()->getAmount());
        $this->assertSame('USD', $product->getPrice()->getCurrency());
        $this->assertSame('active', $product->getStatus());
        $this->assertTrue($product->isActive());
        $this->assertTrue($product->getImages()->isEmpty());
    }

    public function test_product_activate_and_deactivate(): void
    {
        $sku = new \Modules\Core\Domain\ValueObjects\Sku('PROD-002');
        $price = new \Modules\Core\Domain\ValueObjects\Money(10.0, 'USD');

        $product = new Product(tenantId: 1, sku: $sku, name: 'Test', price: $price, status: 'inactive');

        $this->assertFalse($product->isActive());

        $product->activate();
        $this->assertTrue($product->isActive());
        $this->assertSame('active', $product->getStatus());

        $product->deactivate();
        $this->assertFalse($product->isActive());
        $this->assertSame('inactive', $product->getStatus());
    }

    public function test_product_update_details(): void
    {
        $sku = new \Modules\Core\Domain\ValueObjects\Sku('PROD-003');
        $price = new \Modules\Core\Domain\ValueObjects\Money(5.0, 'USD');
        $product = new Product(tenantId: 1, sku: $sku, name: 'Old Name', price: $price);

        $newPrice = new \Modules\Core\Domain\ValueObjects\Money(15.0, 'EUR');
        $product->updateDetails('New Name', $newPrice, 'New description', 'NewCategory', ['color' => 'blue'], null);

        $this->assertSame('New Name', $product->getName());
        $this->assertSame(15.0, $product->getPrice()->getAmount());
        $this->assertSame('EUR', $product->getPrice()->getCurrency());
        $this->assertSame('New description', $product->getDescription());
        $this->assertSame('NewCategory', $product->getCategory());
        $this->assertSame(['color' => 'blue'], $product->getAttributes());
    }

    public function test_product_image_entity_can_be_constructed(): void
    {
        $image = new ProductImage(
            tenantId: 1,
            productId: 5,
            uuid: 'test-uuid',
            name: 'front.jpg',
            filePath: 'products/5/front.jpg',
            mimeType: 'image/jpeg',
            size: 102400,
            sortOrder: 0,
            isPrimary: true,
        );

        $this->assertNull($image->getId());
        $this->assertSame(1, $image->getTenantId());
        $this->assertSame(5, $image->getProductId());
        $this->assertSame('test-uuid', $image->getUuid());
        $this->assertSame('front.jpg', $image->getName());
        $this->assertTrue($image->isPrimary());
        $this->assertSame(0, $image->getSortOrder());
    }

    public function test_product_get_primary_image(): void
    {
        $sku = new \Modules\Core\Domain\ValueObjects\Sku('PROD-004');
        $price = new \Modules\Core\Domain\ValueObjects\Money(1.0, 'USD');
        $product = new Product(tenantId: 1, sku: $sku, name: 'Test', price: $price);

        $this->assertNull($product->getPrimaryImage());

        $img1 = new ProductImage(1, 1, 'uuid-1', 'a.jpg', 'path/a.jpg', 'image/jpeg', 100, 1, false);
        $img2 = new ProductImage(1, 1, 'uuid-2', 'b.jpg', 'path/b.jpg', 'image/jpeg', 200, 0, true);

        $product->addImage($img1);
        $product->addImage($img2);

        // getPrimaryImage should return the one with isPrimary=true
        $primary = $product->getPrimaryImage();
        $this->assertNotNull($primary);
        $this->assertSame('uuid-2', $primary->getUuid());
    }

    // ── Routes file ───────────────────────────────────────────────────────────

    public function test_product_routes_file_exists(): void
    {
        $path = dirname(__DIR__, 2).'/app/Modules/Product/routes/api.php';
        $this->assertTrue(file_exists($path), 'Product routes/api.php must exist.');
    }

    // ── Migrations ────────────────────────────────────────────────────────────

    public function test_product_migration_files_exist(): void
    {
        $dir   = dirname(__DIR__, 2).'/app/Modules/Product/database/migrations';
        $files = glob($dir.'/*.php');
        $this->assertGreaterThanOrEqual(2, count($files), 'At least 2 product migration files must exist.');
    }

    // ── Value Objects ─────────────────────────────────────────────────────────

    public function test_product_type_value_object_class_exists(): void
    {
        $this->assertTrue(class_exists(ProductType::class));
    }

    public function test_unit_of_measure_value_object_class_exists(): void
    {
        $this->assertTrue(class_exists(UnitOfMeasure::class));
    }

    public function test_product_type_valid_values(): void
    {
        foreach (ProductType::VALID_TYPES as $type) {
            $pt = new ProductType($type);
            $this->assertSame($type, $pt->value());
        }
    }

    public function test_product_type_invalid_value_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ProductType('invalid_type');
    }

    public function test_product_type_predicates(): void
    {
        $this->assertTrue((new ProductType('physical'))->isPhysical());
        $this->assertTrue((new ProductType('service'))->isService());
        $this->assertTrue((new ProductType('digital'))->isDigital());
        $this->assertTrue((new ProductType('combo'))->isCombo());
        $this->assertTrue((new ProductType('variable'))->isVariable());
    }

    public function test_unit_of_measure_valid_types(): void
    {
        foreach (UnitOfMeasure::VALID_TYPES as $type) {
            $uom = new UnitOfMeasure('kg', $type, 1.0);
            $this->assertSame('kg', $uom->getUnit());
            $this->assertSame($type, $uom->getType());
            $this->assertSame(1.0, $uom->getConversionFactor());
        }
    }

    public function test_unit_of_measure_invalid_type_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new UnitOfMeasure('kg', 'invalid', 1.0);
    }

    public function test_unit_of_measure_invalid_conversion_factor_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new UnitOfMeasure('kg', 'buying', 0.0);
    }

    public function test_unit_of_measure_predicates(): void
    {
        $buying    = new UnitOfMeasure('pcs', 'buying', 1.0);
        $selling   = new UnitOfMeasure('pcs', 'selling', 1.0);
        $inventory = new UnitOfMeasure('kg', 'inventory', 0.5);

        $this->assertTrue($buying->isBuying());
        $this->assertFalse($buying->isSelling());
        $this->assertTrue($selling->isSelling());
        $this->assertTrue($inventory->isInventory());
    }

    public function test_unit_of_measure_to_array_and_from_array(): void
    {
        $uom   = new UnitOfMeasure('box', 'buying', 12.0);
        $array = $uom->toArray();

        $this->assertSame('box', $array['unit']);
        $this->assertSame('buying', $array['type']);
        $this->assertSame(12.0, $array['conversion_factor']);

        $restored = UnitOfMeasure::fromArray($array);
        $this->assertTrue($uom->equals($restored));
    }

    // ── Product type & UoM integration ───────────────────────────────────────

    public function test_product_entity_defaults_to_physical_type(): void
    {
        $sku     = new \Modules\Core\Domain\ValueObjects\Sku('PROD-T01');
        $price   = new \Modules\Core\Domain\ValueObjects\Money(10.0, 'USD');
        $product = new Product(tenantId: 1, sku: $sku, name: 'Default Type', price: $price);

        $this->assertSame('physical', $product->getType()->value());
        $this->assertSame([], $product->getUnitsOfMeasure());
    }

    public function test_product_entity_accepts_all_types(): void
    {
        $sku   = new \Modules\Core\Domain\ValueObjects\Sku('PROD-T02');
        $price = new \Modules\Core\Domain\ValueObjects\Money(10.0, 'USD');

        foreach (ProductType::VALID_TYPES as $type) {
            $product = new Product(tenantId: 1, sku: $sku, name: 'Type Test', price: $price, type: $type);
            $this->assertSame($type, $product->getType()->value());
        }
    }

    public function test_product_entity_with_units_of_measure(): void
    {
        $sku   = new \Modules\Core\Domain\ValueObjects\Sku('PROD-T03');
        $price = new \Modules\Core\Domain\ValueObjects\Money(10.0, 'USD');

        $buyingUom    = new UnitOfMeasure('pcs', 'buying', 1.0);
        $sellingUom   = new UnitOfMeasure('pcs', 'selling', 1.0);
        $inventoryUom = new UnitOfMeasure('box', 'inventory', 12.0);

        $product = new Product(
            tenantId: 1,
            sku: $sku,
            name: 'UoM Test',
            price: $price,
            unitsOfMeasure: [$buyingUom, $sellingUom, $inventoryUom],
        );

        $this->assertCount(3, $product->getUnitsOfMeasure());
        $this->assertSame('pcs', $product->getBuyingUnit()->getUnit());
        $this->assertSame('pcs', $product->getSellingUnit()->getUnit());
        $this->assertSame('box', $product->getInventoryUnit()->getUnit());
        $this->assertSame(12.0, $product->getInventoryUnit()->getConversionFactor());
    }

    public function test_product_entity_uom_helpers_return_null_when_not_set(): void
    {
        $sku     = new \Modules\Core\Domain\ValueObjects\Sku('PROD-T04');
        $price   = new \Modules\Core\Domain\ValueObjects\Money(10.0, 'USD');
        $product = new Product(tenantId: 1, sku: $sku, name: 'No UoM', price: $price);

        $this->assertNull($product->getBuyingUnit());
        $this->assertNull($product->getSellingUnit());
        $this->assertNull($product->getInventoryUnit());
    }

    public function test_product_update_details_changes_type_and_uom(): void
    {
        $sku     = new \Modules\Core\Domain\ValueObjects\Sku('PROD-T05');
        $price   = new \Modules\Core\Domain\ValueObjects\Money(5.0, 'USD');
        $product = new Product(tenantId: 1, sku: $sku, name: 'Original', price: $price, type: 'physical');

        $newPrice = new \Modules\Core\Domain\ValueObjects\Money(20.0, 'USD');
        $newUom   = new UnitOfMeasure('L', 'selling', 1.0);

        $product->updateDetails(
            name: 'Updated',
            price: $newPrice,
            description: null,
            category: null,
            attributes: null,
            metadata: null,
            type: 'service',
            unitsOfMeasure: [$newUom],
        );

        $this->assertSame('Updated', $product->getName());
        $this->assertSame('service', $product->getType()->value());
        $this->assertCount(1, $product->getUnitsOfMeasure());
        $this->assertSame('L', $product->getSellingUnit()->getUnit());
    }

    public function test_product_update_details_clears_uom_when_empty_array_passed(): void
    {
        $sku     = new \Modules\Core\Domain\ValueObjects\Sku('PROD-T06');
        $price   = new \Modules\Core\Domain\ValueObjects\Money(5.0, 'USD');
        $buyUom  = new UnitOfMeasure('pcs', 'buying', 1.0);
        $product = new Product(
            tenantId: 1, sku: $sku, name: 'Has UoM', price: $price,
            unitsOfMeasure: [$buyUom],
        );

        $this->assertCount(1, $product->getUnitsOfMeasure());

        // Passing empty array should clear the list
        $product->updateDetails(
            name: 'Has UoM',
            price: $price,
            description: null,
            category: null,
            attributes: null,
            metadata: null,
            unitsOfMeasure: [],
        );

        $this->assertCount(0, $product->getUnitsOfMeasure());
    }

    public function test_product_update_details_preserves_uom_when_null_passed(): void
    {
        $sku     = new \Modules\Core\Domain\ValueObjects\Sku('PROD-T07');
        $price   = new \Modules\Core\Domain\ValueObjects\Money(5.0, 'USD');
        $buyUom  = new UnitOfMeasure('pcs', 'buying', 1.0);
        $product = new Product(
            tenantId: 1, sku: $sku, name: 'Preserve UoM', price: $price,
            unitsOfMeasure: [$buyUom],
        );

        // Passing null for unitsOfMeasure should NOT change existing UoMs
        $product->updateDetails(
            name: 'Preserve UoM',
            price: $price,
            description: null,
            category: null,
            attributes: null,
            metadata: null,
            unitsOfMeasure: null,
        );

        $this->assertCount(1, $product->getUnitsOfMeasure());
        $this->assertSame('pcs', $product->getBuyingUnit()->getUnit());
    }
}

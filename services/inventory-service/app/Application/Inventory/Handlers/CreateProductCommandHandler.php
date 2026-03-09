<?php

declare(strict_types=1);

namespace App\Application\Inventory\Handlers;

use App\Application\Inventory\Commands\CreateProductCommand;
use App\Domain\Inventory\Entities\Product;
use App\Domain\Inventory\Enums\ProductStatus;
use App\Domain\Inventory\Events\ProductCreated;
use App\Domain\Inventory\Repositories\ProductRepositoryInterface;
use App\Domain\Inventory\ValueObjects\Money;
use App\Domain\Inventory\ValueObjects\Sku;
use App\Domain\Inventory\ValueObjects\StockQuantity;
use App\Domain\Inventory\Entities\Category;
use App\Domain\Inventory\Repositories\CategoryRepositoryInterface;
use App\Shared\Contracts\MessageBrokerInterface;
use DateTimeImmutable;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Handles the CreateProductCommand.
 *
 * Validates uniqueness of the SKU per tenant, creates the domain entity,
 * persists it, and publishes a ProductCreated event.
 */
final class CreateProductCommandHandler
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly CategoryRepositoryInterface $categoryRepository,
        private readonly MessageBrokerInterface $messageBroker,
    ) {}

    /**
     * Execute the command and return the created product as an array.
     *
     * @throws RuntimeException When the SKU already exists within the tenant.
     *
     * @return array<string, mixed>
     */
    public function handle(CreateProductCommand $command): array
    {
        // Guard: SKU must be unique per tenant.
        $existing = $this->productRepository->findBySku($command->sku, $command->tenantId);

        if ($existing !== null) {
            throw new RuntimeException(
                "SKU '{$command->sku}' already exists for tenant {$command->tenantId}."
            );
        }

        // Resolve category if provided.
        $category = null;
        if ($command->categoryId !== null) {
            $categoryData = $this->categoryRepository->findById($command->categoryId);
            if ($categoryData !== null) {
                $category = Category::fromArray($categoryData);
            }
        }

        // Build the domain entity.
        $product = new Product(
            id: Str::uuid()->toString(),
            tenantId: $command->tenantId,
            sku: new Sku($command->sku),
            name: $command->name,
            description: $command->description,
            category: $category,
            price: new Money($command->price, $command->currency),
            costPrice: new Money($command->costPrice, $command->currency),
            stockQuantity: new StockQuantity($command->stockQuantity),
            minStockLevel: $command->minStockLevel,
            maxStockLevel: $command->maxStockLevel,
            unit: $command->unit,
            barcode: $command->barcode,
            status: ProductStatus::from($command->status),
            isActive: $command->status === 'active',
            tags: $command->tags,
            attributes: $command->attributes,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );

        // Persist.
        $this->productRepository->create($this->mapToDbArray($product));

        // Reload to get any DB-generated values.
        $saved = $this->productRepository->findById($product->id) ?? $product->toArray();

        // Fire domain event.
        $event = new ProductCreated(
            productId: $product->id,
            tenantId: $command->tenantId,
            sku: (string) $product->sku,
            name: $product->name,
        );

        Event::dispatch($event);

        // Publish to message broker (fire-and-forget).
        try {
            $this->messageBroker->publish('inventory.events', $event->toArray());
        } catch (\Throwable $e) {
            // Log but do not fail the request.
            logger()->error('[CreateProductCommandHandler] Broker publish failed', [
                'error' => $e->getMessage(),
            ]);
        }

        return is_array($saved) ? $saved : $product->toArray();
    }

    /**
     * Map the Product entity to a persistence array.
     *
     * @return array<string, mixed>
     */
    private function mapToDbArray(Product $product): array
    {
        return [
            'id'               => $product->id,
            'tenant_id'        => $product->tenantId,
            'sku'              => (string) $product->sku,
            'name'             => $product->name,
            'description'      => $product->description,
            'category_id'      => $product->category?->id,
            'price'            => $product->price->amount,
            'cost_price'       => $product->costPrice->amount,
            'currency'         => $product->price->currency,
            'stock_quantity'   => $product->stockQuantity->value,
            'reserved_quantity'=> 0,
            'min_stock_level'  => $product->minStockLevel,
            'max_stock_level'  => $product->maxStockLevel,
            'unit'             => $product->unit,
            'barcode'          => $product->barcode,
            'status'           => $product->status->value,
            'is_active'        => $product->isActive,
            'tags'             => $product->tags,
            'attributes'       => $product->attributes,
            'created_at'       => $product->createdAt->format('Y-m-d H:i:s'),
            'updated_at'       => $product->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}

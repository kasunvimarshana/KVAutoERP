<?php

declare(strict_types=1);

namespace App\Application\Inventory\Handlers;

use App\Application\Inventory\Commands\UpdateProductCommand;
use App\Domain\Inventory\Events\ProductUpdated;
use App\Domain\Inventory\Repositories\ProductRepositoryInterface;
use App\Shared\Contracts\MessageBrokerInterface;
use DateTimeImmutable;
use Illuminate\Support\Facades\Event;
use RuntimeException;

/**
 * Handles UpdateProductCommand.
 *
 * Applies the patch to only the supplied fields, records which fields changed,
 * and fires a ProductUpdated event.
 */
final class UpdateProductCommandHandler
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly MessageBrokerInterface $messageBroker,
    ) {}

    /**
     * Execute the update command.
     *
     * @throws RuntimeException When the product is not found or belongs to another tenant.
     *
     * @return array<string, mixed>
     */
    public function handle(UpdateProductCommand $command): array
    {
        $existing = $this->productRepository->findById($command->productId);

        if ($existing === null || $existing['tenant_id'] !== $command->tenantId) {
            throw new RuntimeException(
                "Product {$command->productId} not found for tenant {$command->tenantId}."
            );
        }

        // Build an array of only the non-null fields supplied in the command.
        $updates      = [];
        $changedFields = [];

        $fieldMap = [
            'name'           => 'name',
            'description'    => 'description',
            'categoryId'     => 'category_id',
            'price'          => 'price',
            'costPrice'      => 'cost_price',
            'currency'       => 'currency',
            'minStockLevel'  => 'min_stock_level',
            'maxStockLevel'  => 'max_stock_level',
            'unit'           => 'unit',
            'barcode'        => 'barcode',
            'tags'           => 'tags',
            'attributes'     => 'attributes',
            'status'         => 'status',
            'isActive'       => 'is_active',
        ];

        foreach ($fieldMap as $commandField => $dbField) {
            $value = $command->$commandField;
            if ($value !== null) {
                $updates[$dbField] = $value;
                $changedFields[]   = $dbField;
            }
        }

        if (empty($updates)) {
            return $existing;
        }

        $updates['updated_at'] = (new DateTimeImmutable())->format('Y-m-d H:i:s');

        $updated = $this->productRepository->update($command->productId, $updates);

        // Fire domain event.
        $event = new ProductUpdated(
            productId: $command->productId,
            tenantId: $command->tenantId,
            changedFields: $changedFields,
        );

        Event::dispatch($event);

        try {
            $this->messageBroker->publish('inventory.events', $event->toArray());
        } catch (\Throwable $e) {
            logger()->error('[UpdateProductCommandHandler] Broker publish failed', [
                'error' => $e->getMessage(),
            ]);
        }

        return $updated;
    }
}

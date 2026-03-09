<?php

declare(strict_types=1);

namespace App\Services;

use App\Application\Inventory\Commands\AdjustStockCommand;
use App\Application\Inventory\Commands\CreateProductCommand;
use App\Application\Inventory\Commands\DeleteProductCommand;
use App\Application\Inventory\Commands\ReleaseStockCommand;
use App\Application\Inventory\Commands\ReserveStockCommand;
use App\Application\Inventory\Commands\UpdateProductCommand;
use App\Application\Inventory\Handlers\AdjustStockCommandHandler;
use App\Application\Inventory\Handlers\CreateProductCommandHandler;
use App\Application\Inventory\Handlers\ListProductsQueryHandler;
use App\Application\Inventory\Handlers\ReleaseStockCommandHandler;
use App\Application\Inventory\Handlers\ReserveStockCommandHandler;
use App\Application\Inventory\Handlers\UpdateProductCommandHandler;
use App\Application\Inventory\Queries\GetInventoryReportQuery;
use App\Application\Inventory\Queries\ListProductsQuery;
use App\Domain\Inventory\Entities\Product;
use App\Domain\Inventory\Repositories\ProductRepositoryInterface;
use App\Domain\Inventory\Repositories\StockMovementRepositoryInterface;
use App\Domain\Inventory\Services\StockManagementService;
use App\Shared\Base\BaseService;
use App\Shared\Contracts\MessageBrokerInterface;
use DateTimeImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use RuntimeException;

/**
 * Application-level InventoryService.
 *
 * Thin orchestrator that delegates to command/query handlers.
 * Controllers and Console Commands should call this class, not handlers directly.
 */
final class InventoryService extends BaseService
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly StockMovementRepositoryInterface $movementRepository,
        private readonly StockManagementService $stockService,
        private readonly CreateProductCommandHandler $createHandler,
        private readonly UpdateProductCommandHandler $updateHandler,
        private readonly AdjustStockCommandHandler $adjustHandler,
        private readonly ReserveStockCommandHandler $reserveHandler,
        private readonly ReleaseStockCommandHandler $releaseHandler,
        private readonly ListProductsQueryHandler $listHandler,
    ) {
        // BaseService requires a repository — supply the product one.
        parent::__construct($productRepository);
    }

    // ─── Product CRUD ─────────────────────────────────────────────────────────

    /**
     * Create a new product.
     *
     * @return array<string, mixed>
     */
    public function createProduct(CreateProductCommand $command): array
    {
        return $this->createHandler->handle($command);
    }

    /**
     * Update an existing product.
     *
     * @return array<string, mixed>
     */
    public function updateProduct(UpdateProductCommand $command): array
    {
        return $this->updateHandler->handle($command);
    }

    /**
     * Soft-delete a product.
     */
    public function deleteProduct(DeleteProductCommand $command): void
    {
        $existing = $this->productRepository->findById($command->productId);

        if ($existing === null || $existing['tenant_id'] !== $command->tenantId) {
            throw new RuntimeException(
                "Product {$command->productId} not found for tenant {$command->tenantId}."
            );
        }

        $this->productRepository->delete($command->productId);

        $this->logger->info('[InventoryService] Product deleted', [
            'product_id'  => $command->productId,
            'performed_by' => $command->performedBy,
        ]);
    }

    /**
     * Fetch a single product.
     *
     * @return array<string, mixed>
     *
     * @throws RuntimeException When not found.
     */
    public function getProduct(string $id, string $tenantId): array
    {
        $data = $this->productRepository->findById($id);

        if ($data === null || $data['tenant_id'] !== $tenantId) {
            throw new RuntimeException("Product {$id} not found.");
        }

        return $data;
    }

    /**
     * List/search products with filters and pagination.
     *
     * @return array|LengthAwarePaginator
     */
    public function listProducts(ListProductsQuery $query): array|LengthAwarePaginator
    {
        return $this->listHandler->handle($query);
    }

    // ─── Stock Operations ─────────────────────────────────────────────────────

    /**
     * Adjust stock to a new absolute quantity.
     *
     * @return array<string, mixed>  StockMovement array.
     */
    public function adjustStock(AdjustStockCommand $command): array
    {
        return $this->adjustHandler->handle($command);
    }

    /**
     * Reserve stock for an order (Saga participant).
     *
     * @throws RuntimeException When insufficient stock.
     */
    public function reserveStock(ReserveStockCommand $command): bool
    {
        return $this->reserveHandler->handle($command);
    }

    /**
     * Release reserved stock (compensating transaction).
     */
    public function releaseStock(ReleaseStockCommand $command): void
    {
        $this->releaseHandler->handle($command);
    }

    // ─── Reports ─────────────────────────────────────────────────────────────

    /**
     * Build an inventory valuation and summary report.
     *
     * @return array<string, mixed>
     */
    public function getInventoryReport(GetInventoryReportQuery $query): array
    {
        $products = $this->productRepository->findByTenant($query->tenantId);

        $totalProducts  = count($products);
        $totalValue     = 0.0;
        $totalCostValue = 0.0;
        $lowStockCount  = 0;
        $outOfStock     = 0;

        $grouped = [];

        foreach ($products as $raw) {
            $product = Product::fromArray($raw);

            $value      = $product->price->amount * $product->stockQuantity->value;
            $costValue  = $product->costPrice->amount * $product->stockQuantity->value;

            $totalValue     += $value;
            $totalCostValue += $costValue;

            if ($product->isLowStock()) {
                $lowStockCount++;
            }

            if ($product->isOutOfStock()) {
                $outOfStock++;
            }

            // Group by requested dimension.
            $groupKey = match ($query->groupBy) {
                'category' => $raw['category_id'] ?? 'uncategorised',
                'status'   => $raw['status'] ?? 'unknown',
                default    => 'all',
            };

            if (!isset($grouped[$groupKey])) {
                $grouped[$groupKey] = [
                    'key'     => $groupKey,
                    'count'   => 0,
                    'value'   => 0.0,
                ];
            }

            $grouped[$groupKey]['count']++;
            $grouped[$groupKey]['value'] += $value;
        }

        return [
            'tenant_id'       => $query->tenantId,
            'generated_at'    => (new DateTimeImmutable())->format(DATE_ATOM),
            'total_products'  => $totalProducts,
            'total_value'     => round($totalValue, 2),
            'total_cost_value'=> round($totalCostValue, 2),
            'gross_margin'    => $totalValue > 0
                ? round((($totalValue - $totalCostValue) / $totalValue) * 100, 2)
                : 0,
            'low_stock_count' => $lowStockCount,
            'out_of_stock'    => $outOfStock,
            'grouped'         => array_values($grouped),
        ];
    }

    /**
     * Return all low-stock products for a tenant.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getLowStockAlert(string $tenantId): array
    {
        return array_map(
            fn (Product $p) => $p->toArray(),
            $this->stockService->checkAndAlertLowStock($tenantId)
        );
    }
}

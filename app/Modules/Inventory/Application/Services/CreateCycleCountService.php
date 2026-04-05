<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Services;

use Modules\Inventory\Application\Contracts\CreateCycleCountServiceInterface;
use Modules\Inventory\Domain\Entities\CycleCount;
use Modules\Inventory\Domain\RepositoryInterfaces\CycleCountRepositoryInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\StockRepositoryInterface;

class CreateCycleCountService implements CreateCycleCountServiceInterface
{
    public function __construct(
        private readonly CycleCountRepositoryInterface $cycleCountRepo,
        private readonly StockRepositoryInterface $stockRepo,
    ) {}

    public function create(int $tenantId, int $locationId, array $productIds, ?int $createdBy): CycleCount
    {
        $countNumber = 'CC-' . date('Ymd') . '-' . strtoupper(substr(uniqid('', true), -6));

        $cycleCount = $this->cycleCountRepo->create([
            'tenant_id'   => $tenantId,
            'count_number' => $countNumber,
            'location_id' => $locationId,
            'status'      => 'pending',
            'created_by'  => $createdBy,
        ]);

        foreach ($productIds as $productId) {
            $stocks       = $this->stockRepo->findByProduct((int) $productId, $tenantId);
            $locationStock = array_filter(
                $stocks,
                static fn ($s) => $s->locationId === $locationId,
            );
            $systemQty = (float) array_sum(array_map(static fn ($s) => $s->quantity, $locationStock));

            $this->cycleCountRepo->createLine([
                'tenant_id'       => $tenantId,
                'cycle_count_id'  => $cycleCount->id,
                'product_id'      => (int) $productId,
                'variant_id'      => null,
                'system_quantity' => $systemQty,
                'counted_quantity' => null,
            ]);
        }

        return $cycleCount;
    }
}

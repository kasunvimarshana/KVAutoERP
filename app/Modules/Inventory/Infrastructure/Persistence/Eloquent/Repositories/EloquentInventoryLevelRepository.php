<?php
namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Core\Infrastructure\Persistence\Repositories\EloquentRepository;
use Modules\Inventory\Domain\Entities\InventoryLevel;
use Modules\Inventory\Domain\RepositoryInterfaces\InventoryLevelRepositoryInterface;
use Modules\Inventory\Infrastructure\Persistence\Eloquent\Models\InventoryLevelModel;

class EloquentInventoryLevelRepository extends EloquentRepository implements InventoryLevelRepositoryInterface
{
    public function __construct(InventoryLevelModel $model)
    {
        parent::__construct($model);
    }

    public function findById(int $id): ?InventoryLevel
    {
        $model = parent::findById($id);
        return $model ? $this->toEntity($model) : null;
    }

    public function findByProductWarehouseLocation(int $productId, int $warehouseId, int $locationId, ?int $batchId = null): ?InventoryLevel
    {
        $query = $this->model->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->where('location_id', $locationId);

        if ($batchId !== null) {
            $query->where('batch_id', $batchId);
        } else {
            $query->whereNull('batch_id');
        }

        $model = $query->first();
        return $model ? $this->toEntity($model) : null;
    }

    public function findByProduct(int $productId, int $tenantId): array
    {
        return $this->model->where('product_id', $productId)
            ->where('tenant_id', $tenantId)
            ->get()
            ->map(fn($m) => $this->toEntity($m))
            ->all();
    }

    public function findAll(int $tenantId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->where('tenant_id', $tenantId);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    public function create(array $data): InventoryLevel
    {
        $model = parent::create($data);
        return $this->toEntity($model);
    }

    public function update(InventoryLevel $level, array $data): InventoryLevel
    {
        $model = $this->model->findOrFail($level->id);
        $updated = parent::update($model, $data);
        return $this->toEntity($updated);
    }

    public function save(InventoryLevel $level): InventoryLevel
    {
        $model = $this->model->findOrFail($level->id);
        $updated = parent::update($model, [
            'quantity_on_hand'   => $level->quantityOnHand,
            'quantity_reserved'  => $level->quantityReserved,
            'quantity_available' => $level->quantityAvailable,
            'quantity_on_order'  => $level->quantityOnOrder,
        ]);
        return $this->toEntity($updated);
    }

    private function toEntity(object $model): InventoryLevel
    {
        return new InventoryLevel(
            id: $model->id,
            tenantId: $model->tenant_id,
            productId: $model->product_id,
            warehouseId: $model->warehouse_id,
            locationId: $model->location_id,
            quantityOnHand: (float) $model->quantity_on_hand,
            quantityReserved: (float) $model->quantity_reserved,
            quantityAvailable: (float) $model->quantity_available,
            quantityOnOrder: (float) $model->quantity_on_order,
            batchId: $model->batch_id,
            lotId: $model->lot_id,
            serialId: $model->serial_id,
            stockStatus: $model->stock_status,
        );
    }
}

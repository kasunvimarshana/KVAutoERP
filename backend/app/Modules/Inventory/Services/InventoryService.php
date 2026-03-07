<?php

namespace App\Modules\Inventory\Services;

use App\Core\MessageBroker\MessageBrokerInterface;
use App\Core\Pagination\PaginationHelper;
use App\Core\Service\BaseService;
use App\Modules\Inventory\Repositories\InventoryRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class InventoryService extends BaseService
{
    public function __construct(
        InventoryRepository $repository,
        private MessageBrokerInterface $broker
    ) {
        parent::__construct($repository);
    }

    public function index(array $params = []): array
    {
        $query = $this->repository->query()->with('product');
        $this->applyFilters($query, $params);

        return PaginationHelper::paginate($query, $params);
    }

    public function store(array $data): Model
    {
        $inventory = $this->repository->create($data);

        $this->broker->publish('inventory.created', [
            'inventory_id' => $inventory->id,
            'product_id'   => $inventory->product_id,
            'quantity'     => $inventory->quantity,
        ]);

        return $inventory->load('product');
    }

    /**
     * Add / subtract stock.  Pass a positive $delta to add, negative to deduct.
     */
    public function adjustQuantity(int $id, int $delta, string $reason = ''): Model
    {
        $inventory   = $this->repository->findByIdOrFail($id);
        $newQuantity = $inventory->quantity + $delta;

        if ($newQuantity < 0) {
            throw new \InvalidArgumentException('Insufficient stock; adjustment would result in negative quantity.');
        }

        $inventory->update(['quantity' => $newQuantity]);

        $this->broker->publish('inventory.adjusted', [
            'inventory_id' => $id,
            'product_id'   => $inventory->product_id,
            'delta'        => $delta,
            'new_quantity' => $newQuantity,
            'reason'       => $reason,
        ]);

        return $inventory->fresh()->load('product');
    }

    public function reserveQuantity(int $id, int $quantity): Model
    {
        $inventory = $this->repository->findByIdOrFail($id);
        $available = $inventory->quantity - $inventory->reserved_quantity;

        if ($available < $quantity) {
            throw new \InvalidArgumentException(
                "Insufficient available stock. Available: {$available}, requested: {$quantity}."
            );
        }

        $inventory->update(['reserved_quantity' => $inventory->reserved_quantity + $quantity]);

        $this->broker->publish('inventory.reserved', [
            'inventory_id'      => $id,
            'product_id'        => $inventory->product_id,
            'reserved_quantity' => $quantity,
        ]);

        return $inventory->fresh()->load('product');
    }

    public function releaseReservation(int $id, int $quantity): Model
    {
        $inventory   = $this->repository->findByIdOrFail($id);
        $newReserved = max(0, $inventory->reserved_quantity - $quantity);
        $inventory->update(['reserved_quantity' => $newReserved]);

        $this->broker->publish('inventory.reservation_released', [
            'inventory_id'     => $id,
            'released_quantity' => $quantity,
        ]);

        return $inventory->fresh()->load('product');
    }

    /** Cross-service filter: search by product name or other product attributes. */
    protected function applyFilters(Builder $query, array $params): void
    {
        if (!empty($params['product_name'])) {
            $query->whereHas('product', function ($q) use ($params) {
                $q->where('name', 'like', "%{$params['product_name']}%");
            });
        }

        if (!empty($params['product_id'])) {
            $query->where('product_id', $params['product_id']);
        }

        if (!empty($params['warehouse'])) {
            $query->where('warehouse', $params['warehouse']);
        }

        if (array_key_exists('low_stock', $params)) {
            $query->whereRaw('quantity <= min_quantity');
        }
    }
}

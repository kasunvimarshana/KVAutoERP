<?php

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\DTOs\InventoryDTO;
use App\Modules\Inventory\Events\InventoryUpdated;
use App\Modules\Inventory\Models\Inventory;
use App\Modules\Inventory\Repositories\InventoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class InventoryService
{
    public function __construct(
        private InventoryRepositoryInterface $inventoryRepository
    ) {}

    public function list(string $tenantId, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->inventoryRepository->paginate($tenantId, $perPage, $filters);
    }

    public function findById(string $id, string $tenantId): Inventory
    {
        $inventory = $this->inventoryRepository->findById($id, $tenantId);

        if (!$inventory) {
            throw new \RuntimeException("Inventory record not found: {$id}");
        }

        return $inventory;
    }

    public function create(InventoryDTO $dto): Inventory
    {
        $data       = $dto->toArray();
        $data['id'] = Str::uuid()->toString();

        $inventory = $this->inventoryRepository->create($data);

        Event::dispatch(new InventoryUpdated($inventory, 'created'));

        return $inventory;
    }

    public function update(string $id, string $tenantId, array $data): Inventory
    {
        $inventory = $this->findById($id, $tenantId);
        $updated   = $this->inventoryRepository->update($inventory, $data);

        Event::dispatch(new InventoryUpdated($updated, 'updated'));

        return $updated;
    }

    public function adjustStock(string $id, string $tenantId, int $delta, string $reason = ''): Inventory
    {
        $inventory = $this->findById($id, $tenantId);
        $adjusted  = $this->inventoryRepository->adjustQuantity($inventory, $delta);

        Event::dispatch(new InventoryUpdated($adjusted, 'adjusted', ['delta' => $delta, 'reason' => $reason]));

        return $adjusted;
    }

    public function reserveStock(string $id, string $tenantId, int $quantity): bool
    {
        $inventory = $this->findById($id, $tenantId);
        $reserved  = $this->inventoryRepository->reserveQuantity($inventory, $quantity);

        if ($reserved) {
            Event::dispatch(new InventoryUpdated($inventory->refresh(), 'reserved', ['quantity' => $quantity]));
        }

        return $reserved;
    }

    public function releaseStock(string $id, string $tenantId, int $quantity): bool
    {
        $inventory = $this->findById($id, $tenantId);
        return $this->inventoryRepository->releaseReservation($inventory, $quantity);
    }

    public function delete(string $id, string $tenantId): bool
    {
        $inventory = $this->findById($id, $tenantId);
        return $this->inventoryRepository->delete($inventory);
    }
}

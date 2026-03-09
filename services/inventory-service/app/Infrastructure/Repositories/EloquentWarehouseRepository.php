<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domain\Inventory\Entities\Warehouse;
use App\Domain\Inventory\Repositories\WarehouseRepositoryInterface;
use App\Infrastructure\Persistence\Models\Warehouse as WarehouseModel;
use App\Infrastructure\Persistence\Models\WarehouseStock as WarehouseStockModel;
use App\Shared\Base\BaseRepository;
use Illuminate\Support\Str;

final class EloquentWarehouseRepository extends BaseRepository implements WarehouseRepositoryInterface
{
    protected string $modelClass = WarehouseModel::class;

    public function findByCode(string $code, string $tenantId): ?Warehouse
    {
        $row = $this->newQuery()
            ->where('tenant_id', $tenantId)
            ->where('code', strtoupper($code))
            ->first();
        return $row ? Warehouse::fromArray($row->toArray()) : null;
    }

    public function getWarehouseStock(string $warehouseId): array
    {
        return WarehouseStockModel::where('warehouse_id', $warehouseId)
            ->with('product')
            ->get()
            ->toArray();
    }

    public function create(array $data): array
    {
        $data['id']   = $data['id'] ?? Str::uuid()->toString();
        $data['code'] = strtoupper($data['code'] ?? '');
        return parent::create($data);
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use KvEnterprise\SharedKernel\Models\TenantAwareModel;

/**
 * Warehouse domain entity.
 *
 * Represents a physical or virtual storage facility belonging to a tenant.
 * Warehouses contain zero or more bins (storage locations).
 *
 * @property string      $id
 * @property string      $tenant_id
 * @property string      $organization_id
 * @property string|null $branch_id
 * @property string      $code
 * @property string      $name
 * @property string|null $description
 * @property string      $type
 * @property string      $status
 * @property string|null $address_line1
 * @property string|null $address_line2
 * @property string|null $city
 * @property string|null $state
 * @property string|null $country
 * @property string|null $postal_code
 * @property bool        $is_default
 * @property array|null  $metadata
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property string|null $deleted_by
 */
final class Warehouse extends TenantAwareModel
{
    use SoftDeletes;

    /** @var string */
    protected $table = 'warehouses';

    /** @var array<string, string> */
    protected $casts = [
        'is_default'  => 'boolean',
        'metadata'    => 'array',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'deleted_at'  => 'datetime',
    ];

    /** Valid warehouse types. */
    public const TYPES = ['standard', 'bonded', 'transit', 'virtual', 'consignment'];

    /** Valid warehouse statuses. */
    public const STATUSES = ['active', 'inactive', 'archived'];

    /**
     * Storage bins within this warehouse.
     *
     * @return HasMany<WarehouseBin>
     */
    public function bins(): HasMany
    {
        return $this->hasMany(WarehouseBin::class, 'warehouse_id');
    }

    /**
     * Current stock items stored in this warehouse.
     *
     * @return HasMany<StockItem>
     */
    public function stockItems(): HasMany
    {
        return $this->hasMany(StockItem::class, 'warehouse_id');
    }

    /**
     * Stock transfers originating from this warehouse.
     *
     * @return HasMany<StockTransfer>
     */
    public function outboundTransfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'from_warehouse_id');
    }

    /**
     * Stock transfers destined for this warehouse.
     *
     * @return HasMany<StockTransfer>
     */
    public function inboundTransfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'to_warehouse_id');
    }
}

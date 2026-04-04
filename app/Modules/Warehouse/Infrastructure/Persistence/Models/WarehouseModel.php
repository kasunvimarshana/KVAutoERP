<?php

declare(strict_types=1);

namespace Modules\Warehouse\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;

class WarehouseModel extends BaseModel
{
    protected $table = 'warehouses';

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'type',
        'address',
        'is_active',
        'manager_user_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'id'              => 'int',
        'tenant_id'       => 'int',
        'is_active'       => 'boolean',
        'address'         => 'array',
        'manager_user_id' => 'int',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
        'deleted_at'      => 'datetime',
    ];

    public function locations(): HasMany
    {
        return $this->hasMany(WarehouseLocationModel::class, 'warehouse_id');
    }
}

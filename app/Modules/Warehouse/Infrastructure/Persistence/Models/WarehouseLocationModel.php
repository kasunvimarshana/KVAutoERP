<?php

declare(strict_types=1);

namespace Modules\Warehouse\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;

class WarehouseLocationModel extends BaseModel
{
    protected $table = 'warehouse_locations';

    protected $fillable = [
        'tenant_id',
        'warehouse_id',
        'parent_id',
        'name',
        'code',
        'type',
        'barcode',
        'capacity',
        'is_active',
        'level',
        'path',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'id'           => 'int',
        'tenant_id'    => 'int',
        'warehouse_id' => 'int',
        'parent_id'    => 'int',
        'is_active'    => 'boolean',
        'capacity'     => 'float',
        'level'        => 'int',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
        'deleted_at'   => 'datetime',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(WarehouseModel::class, 'warehouse_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}

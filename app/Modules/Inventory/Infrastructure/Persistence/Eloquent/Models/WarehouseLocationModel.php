<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

final class WarehouseLocationModel extends BaseModel
{
    use HasTenant;

    protected $table = 'warehouse_locations';

    protected $fillable = [
        'tenant_id',
        'warehouse_id',
        'parent_id',
        'name',
        'code',
        'type',
        'path',
        'level',
        'barcode',
        'is_active',
    ];

    protected $casts = [
        'id'           => 'int',
        'tenant_id'    => 'int',
        'warehouse_id' => 'int',
        'parent_id'    => 'int',
        'level'        => 'int',
        'is_active'    => 'bool',
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

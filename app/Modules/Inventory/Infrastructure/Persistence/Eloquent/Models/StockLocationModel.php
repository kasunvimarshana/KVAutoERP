<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class StockLocationModel extends BaseModel
{
    use HasTenant;

    protected $table = 'stock_locations';

    protected $fillable = [
        'tenant_id',
        'warehouse_id',
        'code',
        'name',
        'type',
        'parent_id',
        'path',
        'level',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'bool',
        'level'     => 'int',
        'metadata'  => 'array',
    ];
}

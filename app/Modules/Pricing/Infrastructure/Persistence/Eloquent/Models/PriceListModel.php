<?php

declare(strict_types=1);

namespace Modules\Pricing\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class PriceListModel extends BaseModel
{
    use HasTenant;

    protected $table = 'price_lists';

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'currency',
        'is_default',
        'is_active',
        'start_date',
        'end_date',
        'description',
    ];

    protected $casts = [
        'id'         => 'int',
        'tenant_id'  => 'int',
        'is_default' => 'boolean',
        'is_active'  => 'boolean',
        'start_date' => 'date',
        'end_date'   => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}

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
        'valid_from',
        'valid_to',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'bool',
        'is_active'  => 'bool',
        'valid_from' => 'date',
        'valid_to'   => 'date',
    ];
}

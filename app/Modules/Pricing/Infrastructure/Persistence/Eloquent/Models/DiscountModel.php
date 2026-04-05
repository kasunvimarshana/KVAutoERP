<?php

declare(strict_types=1);

namespace Modules\Pricing\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class DiscountModel extends BaseModel
{
    use HasTenant;

    protected $table = 'discounts';

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'type',
        'value',
        'applies_to_type',
        'applies_to_id',
        'min_order_amount',
        'valid_from',
        'valid_to',
        'is_active',
        'usage_limit',
        'usage_count',
    ];

    protected $casts = [
        'is_active'       => 'bool',
        'value'           => 'float',
        'usage_count'     => 'int',
        'usage_limit'     => 'int',
        'min_order_amount'=> 'float',
        'applies_to_id'   => 'int',
        'valid_from'      => 'datetime',
        'valid_to'        => 'datetime',
    ];
}

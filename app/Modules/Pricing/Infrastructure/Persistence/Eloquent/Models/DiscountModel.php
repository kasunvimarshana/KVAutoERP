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
        'min_order_amount',
        'max_uses',
        'used_count',
        'start_date',
        'end_date',
        'is_active',
        'applies_to',
        'product_ids',
        'category_ids',
    ];

    protected $casts = [
        'id'               => 'int',
        'tenant_id'        => 'int',
        'value'            => 'float',
        'min_order_amount' => 'float',
        'max_uses'         => 'int',
        'used_count'       => 'int',
        'is_active'        => 'boolean',
        'start_date'       => 'date',
        'end_date'         => 'date',
        'product_ids'      => 'array',
        'category_ids'     => 'array',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
        'deleted_at'       => 'datetime',
    ];
}

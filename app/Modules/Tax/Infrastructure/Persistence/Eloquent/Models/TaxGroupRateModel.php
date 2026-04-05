<?php

declare(strict_types=1);

namespace Modules\Tax\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class TaxGroupRateModel extends BaseModel
{
    use HasTenant;

    protected $table = 'tax_group_rates';

    protected $fillable = [
        'tenant_id',
        'tax_group_id',
        'name',
        'rate',
        'order',
        'is_compound',
    ];

    protected $casts = [
        'id'           => 'int',
        'tenant_id'    => 'int',
        'tax_group_id' => 'int',
        'rate'         => 'float',
        'order'        => 'int',
        'is_compound'  => 'bool',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
        'deleted_at'   => 'datetime',
    ];
}

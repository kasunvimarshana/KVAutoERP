<?php

declare(strict_types=1);

namespace Modules\Tax\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class TaxRateModel extends BaseModel
{
    use HasTenant;

    protected $table = 'tax_rates';

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'rate',
        'type',
        'is_compound',
        'is_active',
        'country',
        'region',
        'description',
    ];

    protected $casts = [
        'rate'        => 'float',
        'is_compound' => 'bool',
        'is_active'   => 'bool',
    ];
}

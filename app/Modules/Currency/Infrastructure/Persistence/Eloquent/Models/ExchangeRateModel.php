<?php

declare(strict_types=1);

namespace Modules\Currency\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class ExchangeRateModel extends BaseModel
{
    use HasTenant;

    protected $table = 'exchange_rates';

    protected $fillable = [
        'tenant_id',
        'from_currency',
        'to_currency',
        'rate',
        'effective_date',
        'source',
    ];

    protected $casts = [
        'id'             => 'int',
        'tenant_id'      => 'int',
        'rate'           => 'float',
        'effective_date' => 'date',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
        'deleted_at'     => 'datetime',
    ];
}

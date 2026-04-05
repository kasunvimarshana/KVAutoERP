<?php

declare(strict_types=1);

namespace Modules\Currency\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class CurrencyModel extends BaseModel
{
    use HasTenant;

    protected $table = 'currencies';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'symbol',
        'decimal_places',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'id'             => 'int',
        'tenant_id'      => 'int',
        'decimal_places' => 'int',
        'is_default'     => 'bool',
        'is_active'      => 'bool',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
        'deleted_at'     => 'datetime',
    ];
}

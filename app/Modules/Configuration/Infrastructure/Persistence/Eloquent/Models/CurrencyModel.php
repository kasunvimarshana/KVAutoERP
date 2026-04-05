<?php

declare(strict_types=1);

namespace Modules\Configuration\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;

class CurrencyModel extends BaseModel
{
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

<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

final class UnitOfMeasureModel extends BaseModel
{
    use HasTenant;

    protected $table = 'units_of_measure';

    protected $fillable = [
        'tenant_id',
        'name',
        'abbreviation',
        'type',
        'base_unit_factor',
        'is_base_unit',
        'is_active',
    ];

    protected $casts = [
        'id'               => 'int',
        'tenant_id'        => 'int',
        'base_unit_factor' => 'float',
        'is_base_unit'     => 'bool',
        'is_active'        => 'bool',
    ];
}

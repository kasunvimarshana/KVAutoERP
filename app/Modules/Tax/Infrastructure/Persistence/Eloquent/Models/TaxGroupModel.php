<?php

declare(strict_types=1);

namespace Modules\Tax\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class TaxGroupModel extends BaseModel
{
    use HasTenant;

    protected $table = 'tax_groups';

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'description',
        'is_compound',
        'is_active',
    ];

    protected $casts = [
        'id'          => 'int',
        'tenant_id'   => 'int',
        'is_compound' => 'bool',
        'is_active'   => 'bool',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'deleted_at'  => 'datetime',
    ];
}

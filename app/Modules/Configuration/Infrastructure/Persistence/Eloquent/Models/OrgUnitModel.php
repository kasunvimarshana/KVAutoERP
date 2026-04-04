<?php

declare(strict_types=1);

namespace Modules\Configuration\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;

class OrgUnitModel extends BaseModel
{
    protected $table = 'org_units';

    protected $fillable = [
        'tenant_id',
        'parent_id',
        'name',
        'code',
        'type',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'id' => 'int',
        'tenant_id' => 'int',
        'parent_id' => 'int',
        'is_active' => 'bool',
        'sort_order' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}

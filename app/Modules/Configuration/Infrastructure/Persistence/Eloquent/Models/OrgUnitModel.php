<?php

declare(strict_types=1);

namespace Modules\Configuration\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasUuid;

class OrgUnitModel extends BaseModel
{
    use HasTenant, HasUuid, SoftDeletes;

    protected $table = 'org_units';

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'type',
        'parent_id',
        'path',
        'level',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'metadata'   => 'array',
        'is_active'  => 'boolean',
        'level'      => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}

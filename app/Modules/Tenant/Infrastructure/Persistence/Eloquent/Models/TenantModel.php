<?php

declare(strict_types=1);

namespace Modules\Tenant\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;

class TenantModel extends BaseModel
{
    protected $table = 'tenants';

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'database',
        'status',
        'plan',
        'locale',
        'timezone',
        'currency',
        'settings',
        'trial_ends_at',
        'suspended_at',
    ];

    protected $casts = [
        'id' => 'int',
        'settings' => 'array',
        'trial_ends_at' => 'datetime',
        'suspended_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}

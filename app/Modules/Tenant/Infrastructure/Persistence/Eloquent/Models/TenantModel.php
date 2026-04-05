<?php

declare(strict_types=1);

namespace Modules\Tenant\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;

class TenantModel extends BaseModel
{
    protected $table = 'tenants';

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'plan',
        'status',
        'settings',
    ];

    protected $casts = [
        'id'         => 'int',
        'settings'   => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function settings(): HasMany
    {
        return $this->hasMany(TenantSettingModel::class, 'tenant_id');
    }
}

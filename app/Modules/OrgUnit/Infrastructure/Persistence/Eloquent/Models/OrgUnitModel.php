<?php

declare(strict_types=1);

namespace Modules\OrgUnit\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class OrgUnitModel extends BaseModel
{
    use HasTenant;

    protected $table = 'org_units';

    protected $fillable = [
        'tenant_id',
        'parent_id',
        'name',
        'code',
        'type',
        'path',
        'level',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'id'         => 'int',
        'tenant_id'  => 'int',
        'parent_id'  => 'int',
        'level'      => 'int',
        'is_active'  => 'bool',
        'metadata'   => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(OrgUnitModel::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(OrgUnitModel::class, 'parent_id');
    }
}

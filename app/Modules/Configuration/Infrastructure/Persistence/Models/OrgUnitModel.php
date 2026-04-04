<?php

declare(strict_types=1);

namespace Modules\Configuration\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;

class OrgUnitModel extends BaseModel
{
    protected $table = 'org_units';

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'type',
        'parent_id',
        'description',
        'is_active',
        'metadata',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'id'         => 'int',
        'tenant_id'  => 'int',
        'parent_id'  => 'int',
        'is_active'  => 'boolean',
        'metadata'   => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}

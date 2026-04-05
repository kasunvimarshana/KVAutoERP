<?php

declare(strict_types=1);

namespace Modules\Configuration\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class OrgUnitModel extends Model
{
    use HasTenant, SoftDeletes;

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
        'description',
        'metadata',
    ];

    protected $casts = [
        'id'         => 'int',
        'tenant_id'  => 'int',
        'parent_id'  => 'int',
        'level'      => 'int',
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

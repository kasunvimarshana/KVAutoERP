<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use KvEnterprise\SharedKernel\Models\TenantAwareModel;

/**
 * Department domain entity.
 *
 * Represents an organisational department within a tenant hierarchy.
 * Supports self-referential parent→child relationships for nested structures.
 *
 * @property string      $id
 * @property string      $tenant_id
 * @property string      $organization_id
 * @property string|null $branch_id
 * @property string      $name
 * @property string      $code
 * @property string|null $parent_id
 * @property bool        $is_active
 * @property string|null $created_by
 * @property string|null $updated_by
 */
final class Department extends TenantAwareModel
{
    /** @var string */
    protected $table = 'departments';

    /** @var array<string, string> */
    protected $casts = [
        'is_active'  => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The parent department (nullable for top-level departments).
     *
     * @return BelongsTo<Department, Department>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }

    /**
     * Child departments nested under this department.
     *
     * @return HasMany<Department>
     */
    public function children(): HasMany
    {
        return $this->hasMany(Department::class, 'parent_id');
    }
}

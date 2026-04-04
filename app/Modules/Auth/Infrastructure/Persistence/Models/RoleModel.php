<?php

declare(strict_types=1);

namespace Modules\Auth\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;

class RoleModel extends BaseModel
{
    protected $table = 'roles';

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'description',
        'is_system',
    ];

    protected $casts = [
        'id'         => 'int',
        'tenant_id'  => 'int',
        'is_system'  => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            PermissionModel::class,
            'role_permissions',
            'role_id',
            'permission_id',
        );
    }
}

<?php

declare(strict_types=1);

namespace Modules\Auth\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class RoleModel extends BaseModel
{
    use HasTenant;

    protected $table = 'roles';

    protected $fillable = [
        'tenant_id',
        'name',
        'guard_name',
        'permissions',
    ];

    protected $casts = [
        'id'          => 'int',
        'tenant_id'   => 'int',
        'permissions' => 'array',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'deleted_at'  => 'datetime',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            UserModel::class,
            'user_roles',
            'role_id',
            'user_id',
        )->withTimestamps();
    }
}

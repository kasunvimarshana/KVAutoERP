<?php

declare(strict_types=1);

namespace Modules\Auth\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PermissionModel extends Model
{
    protected $table = 'permissions';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'module',
        'action',
    ];

    protected $casts = [
        'id'         => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            RoleModel::class,
            'role_permissions',
            'permission_id',
            'role_id',
        );
    }
}

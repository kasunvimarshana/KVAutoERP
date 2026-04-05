<?php

declare(strict_types=1);

namespace Modules\Auth\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class RoleModel extends Model
{
    use HasTenant, SoftDeletes;

    protected $table = 'roles';

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'permissions',
        'description',
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
        return $this->belongsToMany(UserModel::class, 'role_user', 'role_id', 'user_id');
    }
}

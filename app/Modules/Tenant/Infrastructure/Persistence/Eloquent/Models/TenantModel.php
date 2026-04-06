<?php
declare(strict_types=1);
namespace Modules\Tenant\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasUuid;

class TenantModel extends BaseModel
{
    use HasUuid;

    protected $table = 'tenants';

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'email',
        'phone',
        'address',
        'is_active',
        'plan_id',
        'settings',
    ];

    protected $casts = [
        'id'         => 'int',
        'is_active'  => 'bool',
        'settings'   => 'array',
        'address'    => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}

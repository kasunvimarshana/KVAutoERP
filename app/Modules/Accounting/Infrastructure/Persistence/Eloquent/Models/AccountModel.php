<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class AccountModel extends BaseModel
{
    use HasTenant;

    protected $table = 'accounts';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'type',
        'normal_balance',
        'parent_id',
        'is_active',
        'description',
        'metadata',
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
}

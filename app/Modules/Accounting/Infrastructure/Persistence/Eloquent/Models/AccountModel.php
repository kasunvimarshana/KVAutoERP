<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasUuid;

class AccountModel extends BaseModel
{
    use HasTenant, HasUuid, SoftDeletes;

    protected $table = 'accounts';

    protected $fillable = [
        'tenant_id', 'code', 'name', 'type', 'parent_id',
        'is_active', 'opening_balance', 'current_balance', 'currency', 'description',
    ];

    protected $casts = [
        'is_active'       => 'boolean',
        'opening_balance' => 'float',
        'current_balance' => 'float',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
        'deleted_at'      => 'datetime',
    ];
}

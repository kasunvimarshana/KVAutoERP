<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class BankAccountModel extends BaseModel
{
    use HasTenant;

    protected $table = 'bank_accounts';

    protected $fillable = [
        'tenant_id',
        'name',
        'account_number',
        'account_type',
        'currency',
        'balance',
        'linked_account_id',
        'is_active',
        'last_synced_at',
    ];

    protected $casts = [
        'id'                => 'int',
        'tenant_id'         => 'int',
        'linked_account_id' => 'int',
        'balance'           => 'float',
        'is_active'         => 'boolean',
        'last_synced_at'    => 'datetime',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
        'deleted_at'        => 'datetime',
    ];
}

<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasUuid;

class BankAccountModel extends BaseModel
{
    use HasTenant, HasUuid, SoftDeletes;

    protected $table = 'bank_accounts';

    protected $fillable = [
        'tenant_id', 'name', 'account_number', 'bank_name', 'account_type',
        'balance', 'currency', 'is_active', 'chart_of_account_id',
    ];

    protected $casts = [
        'balance'    => 'float',
        'is_active'  => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}

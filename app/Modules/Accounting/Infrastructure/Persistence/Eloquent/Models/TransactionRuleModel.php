<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class TransactionRuleModel extends BaseModel
{
    use HasTenant;

    protected $table = 'transaction_rules';

    protected $fillable = [
        'tenant_id',
        'name',
        'conditions',
        'apply_to',
        'category_id',
        'account_id',
        'priority',
        'is_active',
    ];

    protected $casts = [
        'id'          => 'int',
        'tenant_id'   => 'int',
        'category_id' => 'int',
        'account_id'  => 'int',
        'conditions'  => 'array',
        'priority'    => 'int',
        'is_active'   => 'boolean',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'deleted_at'  => 'datetime',
    ];
}

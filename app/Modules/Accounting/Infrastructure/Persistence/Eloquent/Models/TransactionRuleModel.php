<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasUuid;

class TransactionRuleModel extends BaseModel
{
    use HasTenant, HasUuid, SoftDeletes;

    protected $table = 'transaction_rules';

    protected $fillable = [
        'tenant_id', 'name', 'conditions', 'category_id', 'account_id',
        'apply_to', 'priority', 'is_active',
    ];

    protected $casts = [
        'conditions' => 'array',
        'is_active'  => 'boolean',
        'priority'   => 'integer',
    ];
}

<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class TransactionRuleModel extends BaseModel
{
    use HasTenant;

    protected $table = 'accounting_transaction_rules';

    protected $fillable = [
        'tenant_id',
        'name',
        'conditions',
        'account_id',
        'apply_to',
        'priority',
        'is_active',
    ];

    protected $casts = [
        'id'         => 'int',
        'tenant_id'  => 'int',
        'account_id' => 'int',
        'conditions' => 'array',
        'priority'   => 'int',
        'is_active'  => 'bool',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(AccountModel::class, 'account_id');
    }
}

<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class BudgetModel extends BaseModel
{
    use HasTenant;

    protected $table = 'accounting_budgets';

    protected $fillable = [
        'tenant_id',
        'name',
        'account_id',
        'period_type',
        'start_date',
        'end_date',
        'amount',
        'spent',
        'notes',
    ];

    protected $casts = [
        'id'         => 'int',
        'tenant_id'  => 'int',
        'account_id' => 'int',
        'start_date' => 'date',
        'end_date'   => 'date',
        'amount'     => 'float',
        'spent'      => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(AccountModel::class, 'account_id');
    }
}

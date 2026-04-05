<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class BudgetModel extends BaseModel
{
    use HasTenant;

    protected $table = 'budgets';

    protected $fillable = [
        'tenant_id',
        'name',
        'account_id',
        'year',
        'month',
        'amount',
        'spent',
        'notes',
    ];

    protected $casts = [
        'id'         => 'int',
        'tenant_id'  => 'int',
        'account_id' => 'int',
        'year'       => 'int',
        'month'      => 'int',
        'amount'     => 'float',
        'spent'      => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}

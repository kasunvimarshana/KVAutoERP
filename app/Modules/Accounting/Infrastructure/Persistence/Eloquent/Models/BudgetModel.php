<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasUuid;

class BudgetModel extends BaseModel
{
    use HasTenant, HasUuid, SoftDeletes;

    protected $table = 'budgets';

    protected $fillable = [
        'tenant_id', 'name', 'fiscal_year', 'account_id', 'amount',
        'period', 'start_date', 'end_date', 'notes',
    ];

    protected $casts = [
        'fiscal_year' => 'integer',
        'amount'      => 'float',
        'start_date'  => 'date',
        'end_date'    => 'date',
    ];
}

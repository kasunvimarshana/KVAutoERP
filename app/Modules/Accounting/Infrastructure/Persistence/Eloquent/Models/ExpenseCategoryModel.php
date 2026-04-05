<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasUuid;

class ExpenseCategoryModel extends BaseModel
{
    use HasTenant, HasUuid, SoftDeletes;

    protected $table = 'expense_categories';

    protected $fillable = [
        'tenant_id', 'name', 'code', 'parent_id', 'account_id', 'is_active', 'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}

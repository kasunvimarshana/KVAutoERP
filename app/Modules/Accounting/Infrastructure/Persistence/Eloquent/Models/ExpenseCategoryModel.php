<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class ExpenseCategoryModel extends BaseModel
{
    use HasTenant;

    protected $table = 'accounting_expense_categories';

    protected $fillable = [
        'tenant_id',
        'name',
        'account_id',
        'parent_id',
        'color',
        'is_active',
    ];

    protected $casts = [
        'id'         => 'int',
        'tenant_id'  => 'int',
        'account_id' => 'int',
        'parent_id'  => 'int',
        'is_active'  => 'bool',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategoryModel::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ExpenseCategoryModel::class, 'parent_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(AccountModel::class, 'account_id');
    }
}

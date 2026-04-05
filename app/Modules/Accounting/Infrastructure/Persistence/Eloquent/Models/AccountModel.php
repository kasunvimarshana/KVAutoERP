<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class AccountModel extends BaseModel
{
    use HasTenant;

    protected $table = 'accounting_accounts';

    protected $fillable = [
        'tenant_id',
        'parent_id',
        'code',
        'name',
        'type',
        'normal_balance',
        'is_active',
        'description',
    ];

    protected $casts = [
        'id'         => 'int',
        'tenant_id'  => 'int',
        'parent_id'  => 'int',
        'is_active'  => 'bool',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(AccountModel::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(AccountModel::class, 'parent_id');
    }
}

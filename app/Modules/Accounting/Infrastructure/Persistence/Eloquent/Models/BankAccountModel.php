<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class BankAccountModel extends BaseModel
{
    use HasTenant;

    protected $table = 'accounting_bank_accounts';

    protected $fillable = [
        'tenant_id',
        'account_id',
        'name',
        'account_number',
        'account_type',
        'currency_code',
        'current_balance',
        'last_synced_at',
        'is_active',
        'credentials',
    ];

    protected $casts = [
        'id'              => 'int',
        'tenant_id'       => 'int',
        'account_id'      => 'int',
        'current_balance' => 'float',
        'is_active'       => 'bool',
        'last_synced_at'  => 'datetime',
        'credentials'     => 'array',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
        'deleted_at'      => 'datetime',
    ];

    /** @var array<int, string> */
    protected $hidden = ['credentials'];

    public function chartAccount(): BelongsTo
    {
        return $this->belongsTo(AccountModel::class, 'account_id');
    }
}

<?php

declare(strict_types=1);

namespace Modules\Tenant\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantSettingModel extends Model
{
    protected $table = 'tenant_settings';

    protected $fillable = [
        'tenant_id',
        'key',
        'value',
        'group',
    ];

    protected $casts = [
        'id'         => 'int',
        'tenant_id'  => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(TenantModel::class, 'tenant_id');
    }
}

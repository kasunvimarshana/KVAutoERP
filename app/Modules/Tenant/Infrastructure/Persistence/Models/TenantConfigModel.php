<?php

declare(strict_types=1);

namespace Modules\Tenant\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantConfigModel extends Model
{
    protected $table = 'tenant_configs';

    protected $fillable = [
        'tenant_id',
        'key',
        'value',
        'group',
        'is_secret',
    ];

    protected $casts = [
        'id'        => 'int',
        'tenant_id' => 'int',
        'is_secret' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(TenantModel::class, 'tenant_id');
    }
}

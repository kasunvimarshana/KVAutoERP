<?php

declare(strict_types=1);

namespace Modules\Configuration\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class SystemConfigModel extends Model
{
    protected $table = 'system_configs';

    protected $fillable = [
        'tenant_id',
        'key',
        'value',
        'group',
        'description',
        'is_system',
    ];

    protected $casts = [
        'id'         => 'int',
        'tenant_id'  => 'int',
        'is_system'  => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

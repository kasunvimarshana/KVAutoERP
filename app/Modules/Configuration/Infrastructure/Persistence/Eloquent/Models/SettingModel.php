<?php

declare(strict_types=1);

namespace Modules\Configuration\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class SettingModel extends Model
{
    use HasTenant;

    protected $table = 'settings';

    protected $fillable = [
        'tenant_id',
        'key',
        'value',
        'group',
        'type',
        'is_system',
        'description',
    ];

    protected $casts = [
        'id'         => 'int',
        'tenant_id'  => 'int',
        'value'      => 'string',
        'is_system'  => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

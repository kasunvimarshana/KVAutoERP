<?php

declare(strict_types=1);

namespace Modules\Configuration\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasUuid;

class SettingModel extends BaseModel
{
    use HasUuid;

    protected $table = 'settings';

    protected $fillable = [
        'tenant_id',
        'key',
        'value',
        'type',
        'module',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

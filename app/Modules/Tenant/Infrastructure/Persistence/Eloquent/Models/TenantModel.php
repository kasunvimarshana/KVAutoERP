<?php

declare(strict_types=1);

namespace Modules\Tenant\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class TenantModel extends Model
{
    protected $table = 'tenants';

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'status',
        'plan',
        'settings',
        'metadata',
    ];

    protected $casts = [
        'settings'   => 'array',
        'metadata'   => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

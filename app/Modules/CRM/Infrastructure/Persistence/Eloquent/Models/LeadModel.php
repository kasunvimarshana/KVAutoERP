<?php

declare(strict_types=1);

namespace Modules\CRM\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class LeadModel extends BaseModel
{
    use HasTenant;

    protected $table = 'leads';

    protected $fillable = [
        'tenant_id',
        'contact_id',
        'name',
        'email',
        'phone',
        'source',
        'status',
        'value',
        'currency',
        'assigned_to',
        'probability',
        'expected_close_date',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'id'                  => 'int',
        'tenant_id'           => 'int',
        'contact_id'          => 'int',
        'assigned_to'         => 'int',
        'value'               => 'float',
        'probability'         => 'int',
        'expected_close_date' => 'date',
        'metadata'            => 'array',
        'created_at'          => 'datetime',
        'updated_at'          => 'datetime',
        'deleted_at'          => 'datetime',
    ];
}

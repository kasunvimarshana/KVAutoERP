<?php

declare(strict_types=1);

namespace Modules\CRM\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class OpportunityModel extends BaseModel
{
    use HasTenant;

    protected $table = 'opportunities';

    protected $fillable = [
        'tenant_id',
        'lead_id',
        'contact_id',
        'name',
        'stage',
        'value',
        'currency',
        'probability',
        'expected_close_date',
        'assigned_to',
        'description',
    ];

    protected $casts = [
        'id'                  => 'int',
        'tenant_id'           => 'int',
        'lead_id'             => 'int',
        'contact_id'          => 'int',
        'assigned_to'         => 'int',
        'value'               => 'float',
        'probability'         => 'int',
        'expected_close_date' => 'date',
        'created_at'          => 'datetime',
        'updated_at'          => 'datetime',
        'deleted_at'          => 'datetime',
    ];
}

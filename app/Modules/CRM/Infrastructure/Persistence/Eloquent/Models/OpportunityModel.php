<?php

declare(strict_types=1);

namespace Modules\CRM\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

final class OpportunityModel extends BaseModel
{
    use HasTenant;

    protected $table = 'opportunities';

    protected $fillable = [
        'tenant_id', 'contact_id', 'name', 'stage', 'probability', 'value',
        'expected_close_date', 'assigned_to', 'notes', 'lost_reason',
    ];

    protected $casts = [
        'id'                  => 'int',
        'tenant_id'           => 'int',
        'contact_id'          => 'int',
        'probability'         => 'int',
        'value'               => 'float',
        'assigned_to'         => 'int',
        'expected_close_date' => 'date',
        'created_at'          => 'datetime',
        'updated_at'          => 'datetime',
        'deleted_at'          => 'datetime',
    ];
}

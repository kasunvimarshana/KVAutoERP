<?php

declare(strict_types=1);

namespace Modules\CRM\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

final class ActivityModel extends BaseModel
{
    use HasTenant;

    protected $table = 'activities';

    protected $fillable = [
        'tenant_id', 'contact_id', 'opportunity_id', 'lead_id', 'type',
        'subject', 'description', 'status', 'scheduled_at', 'completed_at', 'assigned_to',
    ];

    protected $casts = [
        'id'             => 'int',
        'tenant_id'      => 'int',
        'contact_id'     => 'int',
        'opportunity_id' => 'int',
        'lead_id'        => 'int',
        'assigned_to'    => 'int',
        'scheduled_at'   => 'datetime',
        'completed_at'   => 'datetime',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
        'deleted_at'     => 'datetime',
    ];
}

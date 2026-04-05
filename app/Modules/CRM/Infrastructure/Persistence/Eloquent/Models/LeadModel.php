<?php

declare(strict_types=1);

namespace Modules\CRM\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

final class LeadModel extends BaseModel
{
    use HasTenant;

    protected $table = 'leads';

    protected $fillable = [
        'tenant_id', 'contact_id', 'name', 'email', 'phone', 'company',
        'source', 'status', 'score', 'assigned_to', 'notes', 'expected_value',
    ];

    protected $casts = [
        'id'             => 'int',
        'tenant_id'      => 'int',
        'contact_id'     => 'int',
        'score'          => 'int',
        'assigned_to'    => 'int',
        'expected_value' => 'float',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
        'deleted_at'     => 'datetime',
    ];
}

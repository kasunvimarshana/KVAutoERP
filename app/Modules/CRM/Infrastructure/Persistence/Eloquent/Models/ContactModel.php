<?php

declare(strict_types=1);

namespace Modules\CRM\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class ContactModel extends BaseModel
{
    use HasTenant;

    protected $table = 'contacts';

    protected $fillable = [
        'tenant_id',
        'type',
        'first_name',
        'last_name',
        'email',
        'phone',
        'mobile',
        'company',
        'job_title',
        'address',
        'tags',
        'status',
        'assigned_to',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'id'          => 'int',
        'tenant_id'   => 'int',
        'assigned_to' => 'int',
        'address'     => 'array',
        'tags'        => 'array',
        'metadata'    => 'array',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'deleted_at'  => 'datetime',
    ];
}

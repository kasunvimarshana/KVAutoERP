<?php

declare(strict_types=1);

namespace Modules\CRM\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

final class ContactModel extends BaseModel
{
    use HasTenant;

    protected $table = 'contacts';

    protected $fillable = [
        'tenant_id', 'type', 'name', 'email', 'phone', 'mobile',
        'company', 'position', 'address', 'tax_number', 'currency_code',
        'credit_limit', 'payment_terms', 'notes', 'is_active', 'tags', 'custom_fields',
    ];

    protected $casts = [
        'id'            => 'int',
        'tenant_id'     => 'int',
        'credit_limit'  => 'float',
        'payment_terms' => 'int',
        'is_active'     => 'bool',
        'address'       => 'array',
        'tags'          => 'array',
        'custom_fields' => 'array',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
        'deleted_at'    => 'datetime',
    ];
}

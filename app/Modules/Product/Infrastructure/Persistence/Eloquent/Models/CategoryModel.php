<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class CategoryModel extends BaseModel
{
    use HasTenant;

    protected $table = 'product_categories';

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'parent_id',
        'path',
        'level',
        'description',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'id'        => 'int',
        'tenant_id' => 'int',
        'parent_id' => 'int',
        'level'     => 'int',
        'is_active' => 'boolean',
        'metadata'  => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}

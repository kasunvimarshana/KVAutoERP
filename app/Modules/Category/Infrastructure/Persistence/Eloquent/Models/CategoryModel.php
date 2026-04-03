<?php
declare(strict_types=1);
namespace Modules\Category\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;

class CategoryModel extends BaseModel
{
    protected $table = 'categories';
    protected $fillable = [
        'tenant_id', 'name', 'slug', 'description', 'parent_id',
        'depth', 'path', 'status', 'attributes', 'metadata',
    ];
    protected $casts = [
        'id'         => 'int',
        'tenant_id'  => 'int',
        'parent_id'  => 'int',
        'depth'      => 'int',
        'attributes' => 'array',
        'metadata'   => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}

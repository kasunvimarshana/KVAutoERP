<?php
declare(strict_types=1);
namespace Modules\Category\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;

class CategoryImageModel extends BaseModel
{
    protected $table = 'category_images';
    protected $fillable = [
        'tenant_id', 'category_id', 'uuid', 'name', 'file_path',
        'mime_type', 'size', 'metadata',
    ];
    protected $casts = [
        'id'          => 'int',
        'tenant_id'   => 'int',
        'category_id' => 'int',
        'size'        => 'int',
        'metadata'    => 'array',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'deleted_at'  => 'datetime',
    ];
}

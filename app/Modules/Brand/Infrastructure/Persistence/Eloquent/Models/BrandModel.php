<?php
declare(strict_types=1);
namespace Modules\Brand\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class BrandModel extends BaseModel
{
    protected $table = 'brands';
    protected $fillable = ['tenant_id', 'name', 'slug', 'description', 'website', 'status', 'attributes', 'metadata'];
    protected $casts = ['id' => 'int', 'tenant_id' => 'int', 'attributes' => 'array', 'metadata' => 'array', 'created_at' => 'datetime', 'updated_at' => 'datetime', 'deleted_at' => 'datetime'];
}

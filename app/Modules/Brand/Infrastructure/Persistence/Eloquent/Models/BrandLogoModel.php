<?php
declare(strict_types=1);
namespace Modules\Brand\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class BrandLogoModel extends BaseModel
{
    protected $table = 'brand_logos';
    protected $fillable = ['tenant_id', 'brand_id', 'uuid', 'name', 'file_path', 'mime_type', 'size', 'metadata'];
    protected $casts = ['id' => 'int', 'tenant_id' => 'int', 'brand_id' => 'int', 'size' => 'int', 'metadata' => 'array', 'created_at' => 'datetime', 'updated_at' => 'datetime', 'deleted_at' => 'datetime'];
}

<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;

class ProductCategoryModel extends BaseModel
{
    protected $table = 'product_categories';

    protected $fillable = [
        'tenant_id', 'name', 'slug', 'description', 'parent_id',
        'image', 'is_active', 'sort_order', 'metadata', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'id' => 'int', 'tenant_id' => 'int', 'parent_id' => 'int',
        'is_active' => 'boolean', 'sort_order' => 'int', 'metadata' => 'array',
        'created_at' => 'datetime', 'updated_at' => 'datetime', 'deleted_at' => 'datetime',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}

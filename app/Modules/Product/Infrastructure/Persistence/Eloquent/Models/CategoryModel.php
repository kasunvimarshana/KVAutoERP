<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class CategoryModel extends BaseModel
{
    use HasTenant;

    protected $table = 'categories';

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'parent_id',
        'path',
        'level',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'bool',
        'level'     => 'int',
        'parent_id' => 'int',
    ];

    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}

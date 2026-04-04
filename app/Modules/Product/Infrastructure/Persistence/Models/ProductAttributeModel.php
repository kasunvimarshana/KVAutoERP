<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAttributeModel extends Model
{
    protected $table = 'product_attributes';

    protected $fillable = ['tenant_id', 'name', 'slug', 'type', 'options'];

    protected $casts = [
        'id' => 'int', 'tenant_id' => 'int', 'options' => 'array',
        'created_at' => 'datetime', 'updated_at' => 'datetime',
    ];
}

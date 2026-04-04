<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCategoryClosureModel extends Model
{
    protected $table = 'product_category_closures';

    public $timestamps = false;

    protected $fillable = ['ancestor_id', 'descendant_id', 'depth'];

    protected $casts = [
        'ancestor_id' => 'int', 'descendant_id' => 'int', 'depth' => 'int',
    ];
}

<?php

declare(strict_types=1);

namespace Modules\Barcode\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class LabelTemplateModel extends BaseModel
{
    use HasTenant;

    protected $table = 'label_templates';

    protected $fillable = [
        'tenant_id',
        'name',
        'format',
        'template',
        'width',
        'height',
        'variables',
    ];

    protected $casts = [
        'id'         => 'int',
        'tenant_id'  => 'int',
        'width'      => 'float',
        'height'     => 'float',
        'variables'  => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}

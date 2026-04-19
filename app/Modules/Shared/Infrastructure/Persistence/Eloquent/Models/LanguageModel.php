<?php

declare(strict_types=1);

namespace Modules\Shared\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;

use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasAudit;

class LanguageModel extends BaseModel
{
    use HasAudit;

    protected $table = 'languages';

    protected $fillable = [
        'code',
        'name',
    ];
}

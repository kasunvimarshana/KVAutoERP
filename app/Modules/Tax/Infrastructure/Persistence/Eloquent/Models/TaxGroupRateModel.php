<?php

declare(strict_types=1);

namespace Modules\Tax\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

/**
 * No softDeletes on this join model (no deleted_at column).
 */
class TaxGroupRateModel extends BaseModel
{
    use HasTenant;

    protected $table = 'tax_group_rates';

    protected $fillable = [
        'tenant_id',
        'tax_group_id',
        'tax_rate_id',
        'sort_order',
    ];

    protected $casts = [
        'sort_order'   => 'int',
        'tax_group_id' => 'int',
        'tax_rate_id'  => 'int',
    ];

    /** Override to disable softDeletes from BaseModel if it defines the trait. */
    public function getDeletedAtColumn(): string
    {
        return 'deleted_at';
    }
}

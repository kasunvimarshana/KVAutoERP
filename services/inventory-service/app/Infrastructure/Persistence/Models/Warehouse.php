<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Eloquent model for the warehouses table.
 *
 * @property string       $id
 * @property string       $tenant_id
 * @property string       $name
 * @property string       $code
 * @property array        $address
 * @property bool         $is_active
 * @property int|null     $capacity
 */
class Warehouse extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'warehouses';

    protected $fillable = [
        'id',
        'tenant_id',
        'name',
        'code',
        'address',
        'is_active',
        'capacity',
    ];

    protected $casts = [
        'address'   => 'array',
        'is_active' => 'boolean',
        'capacity'  => 'integer',
    ];

    protected $attributes = [
        'is_active' => true,
        'address'   => '{}',
    ];
}

<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Tenant Eloquent Model.
 *
 * Represents a top-level tenant (organisation) in the multi-tenant system.
 *
 * @property string      $id
 * @property string      $name
 * @property string      $slug
 * @property string|null $domain
 * @property string|null $database_name
 * @property array       $settings
 * @property bool        $is_active
 */
class Tenant extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    /** @var string */
    protected $table = 'tenants';

    /** @var string */
    protected $keyType = 'string';

    /** @var bool */
    public $incrementing = false;

    /** @var array<string> */
    protected $fillable = [
        'id',
        'name',
        'slug',
        'domain',
        'database_name',
        'settings',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'settings'  => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * All users belonging to this tenant.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'tenant_id');
    }
}

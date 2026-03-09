<?php

declare(strict_types=1);

namespace App\Domain\Organization\Entities;

use App\Domain\Tenant\Entities\Tenant;
use App\Domain\User\Entities\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string            $id
 * @property string            $tenant_id
 * @property string|null       $parent_id
 * @property string            $name
 * @property string            $slug
 * @property array|null        $settings
 * @property string            $status
 * @property Carbon            $created_at
 * @property Carbon            $updated_at
 * @property Carbon|null       $deleted_at
 * @property-read Tenant       $tenant
 * @property-read self|null    $parent
 * @property-read Collection   $children
 * @property-read Collection   $users
 */
class Organization extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    public const STATUS_ACTIVE   = 'active';
    public const STATUS_INACTIVE = 'inactive';

    /** @var string */
    protected $table = 'organizations';

    /** @var string */
    protected $primaryKey = 'id';

    /** @var bool */
    public $incrementing = false;

    /** @var string */
    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'tenant_id',
        'parent_id',
        'name',
        'slug',
        'settings',
        'status',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'settings' => 'array',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'organization_id');
    }

    // -------------------------------------------------------------------------
    // Business Methods
    // -------------------------------------------------------------------------

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Get a settings value using dot-notation.
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }

    /**
     * Determine whether this org is a root (top-level) organization.
     */
    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }

    /**
     * Recursively retrieve all descendant organization IDs.
     *
     * @return list<string>
     */
    public function descendantIds(): array
    {
        $ids = [];

        foreach ($this->children as $child) {
            $ids[] = $child->id;
            $ids   = array_merge($ids, $child->descendantIds());
        }

        return $ids;
    }
}

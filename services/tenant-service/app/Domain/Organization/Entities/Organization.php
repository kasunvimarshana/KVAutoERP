<?php

declare(strict_types=1);

namespace App\Domain\Organization\Entities;

use App\Domain\Tenant\Entities\Tenant;
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
 * @property string|null       $description
 * @property string            $status
 * @property array|null        $settings
 * @property array|null        $metadata
 * @property Carbon            $created_at
 * @property Carbon            $updated_at
 * @property Carbon|null       $deleted_at
 * @property-read Tenant       $tenant
 * @property-read self|null    $parent
 * @property-read Collection   $children
 */
class Organization extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    public const STATUS_ACTIVE   = 'active';
    public const STATUS_INACTIVE = 'inactive';

    protected $table = 'organizations';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'tenant_id',
        'parent_id',
        'name',
        'slug',
        'description',
        'status',
        'settings',
        'metadata',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'settings' => 'array',
        'metadata' => 'array',
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

    // -------------------------------------------------------------------------
    // Business Methods
    // -------------------------------------------------------------------------

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }

    /**
     * Return the depth of this node (0 = root).
     */
    public function getDepth(): int
    {
        $depth  = 0;
        $node   = $this;

        while ($node->parent_id !== null) {
            $depth++;
            $node = $node->parent;

            if ($node === null) {
                break;
            }
        }

        return $depth;
    }

    /**
     * Return all ancestors from immediate parent up to root.
     *
     * @return list<self>
     */
    public function getAncestors(): array
    {
        $ancestors = [];
        $node      = $this->parent;

        while ($node !== null) {
            $ancestors[] = $node;
            $node        = $node->parent;
        }

        return $ancestors;
    }

    /**
     * Return all descendants recursively.
     *
     * @return list<self>
     */
    public function getDescendants(): array
    {
        $descendants = [];

        foreach ($this->children as $child) {
            $descendants[] = $child;
            foreach ($child->getDescendants() as $grandchild) {
                $descendants[] = $grandchild;
            }
        }

        return $descendants;
    }

    /**
     * Get a settings value using dot-notation.
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }
}

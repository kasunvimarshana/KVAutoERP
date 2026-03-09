<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Eloquent model for the categories table.
 *
 * @property string       $id
 * @property string       $tenant_id
 * @property string       $name
 * @property string       $slug
 * @property string|null  $parent_id
 * @property string       $description
 * @property bool         $is_active
 */
class Category extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'categories';

    protected $fillable = [
        'id',
        'tenant_id',
        'name',
        'slug',
        'parent_id',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'description' => '',
        'is_active'   => true,
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    /** The parent category (null if root). */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** Direct child categories. */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /** Products in this category. */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}

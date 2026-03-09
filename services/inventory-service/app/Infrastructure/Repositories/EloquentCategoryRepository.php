<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domain\Inventory\Entities\Category;
use App\Domain\Inventory\Repositories\CategoryRepositoryInterface;
use App\Infrastructure\Persistence\Models\Category as CategoryModel;
use App\Shared\Base\BaseRepository;
use Illuminate\Support\Str;

/**
 * Eloquent implementation of CategoryRepositoryInterface.
 */
final class EloquentCategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    protected string $modelClass = CategoryModel::class;

    /** @var array<string> Columns used for full-text search. */
    protected array $searchableColumns = ['name', 'description', 'slug'];

    // ─── CategoryRepositoryInterface ─────────────────────────────────────────

    public function findBySlug(string $slug, string $tenantId): ?Category
    {
        $row = $this->newQuery()
            ->where('tenant_id', $tenantId)
            ->where('slug', $slug)
            ->first();

        return $row ? Category::fromArray($row->toArray()) : null;
    }

    public function findRootCategories(string $tenantId): array
    {
        return $this->newQuery()
            ->where('tenant_id', $tenantId)
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->get()
            ->map(fn ($r) => Category::fromArray($r->toArray()))
            ->all();
    }

    public function findChildren(string $parentId, string $tenantId): array
    {
        return $this->newQuery()
            ->where('tenant_id', $tenantId)
            ->where('parent_id', $parentId)
            ->where('is_active', true)
            ->get()
            ->map(fn ($r) => Category::fromArray($r->toArray()))
            ->all();
    }

    // ─── RepositoryInterface overrides ───────────────────────────────────────

    public function create(array $data): array
    {
        $data['id'] = $data['id'] ?? Str::uuid()->toString();

        // Auto-generate slug from name if not provided.
        if (empty($data['slug']) && !empty($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        return parent::create($data);
    }
}

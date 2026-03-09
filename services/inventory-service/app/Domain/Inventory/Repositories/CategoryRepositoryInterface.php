<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Repositories;

use App\Domain\Inventory\Entities\Category;
use App\Shared\Contracts\RepositoryInterface;

/**
 * Category repository contract.
 */
interface CategoryRepositoryInterface extends RepositoryInterface
{
    /**
     * Find a category by its slug within a tenant.
     */
    public function findBySlug(string $slug, string $tenantId): ?Category;

    /**
     * Return all root categories (no parent) for a tenant.
     *
     * @return Category[]
     */
    public function findRootCategories(string $tenantId): array;

    /**
     * Return all direct children of a given category.
     *
     * @return Category[]
     */
    public function findChildren(string $parentId, string $tenantId): array;
}

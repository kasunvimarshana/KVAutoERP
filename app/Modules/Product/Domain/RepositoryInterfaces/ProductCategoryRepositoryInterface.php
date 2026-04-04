<?php

declare(strict_types=1);

namespace Modules\Product\Domain\RepositoryInterfaces;

use Modules\Product\Domain\Entities\ProductCategory;

interface ProductCategoryRepositoryInterface
{
    public function findById(int $id): ?ProductCategory;
    public function findBySlug(int $tenantId, string $slug): ?ProductCategory;
    public function findAllByTenant(int $tenantId): array;
    public function findDescendants(int $ancestorId): array;
    public function save(ProductCategory $category): ProductCategory;
    public function delete(int $id): void;
    public function buildTree(int $tenantId): array;
}

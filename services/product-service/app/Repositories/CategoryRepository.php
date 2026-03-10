<?php
namespace App\Repositories;
use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;

class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    public function __construct(Category $model) { parent::__construct($model); }
    protected function searchableColumns(): array { return ['name', 'slug', 'description']; }
    protected function sortableColumns(): array { return ['name', 'slug', 'created_at', 'updated_at']; }
}

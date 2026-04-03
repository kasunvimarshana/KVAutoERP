<?php
declare(strict_types=1);
namespace Modules\Brand\Infrastructure\Persistence\Eloquent\Repositories;
use Illuminate\Support\Collection;
use Modules\Brand\Domain\Entities\Brand;
use Modules\Brand\Domain\RepositoryInterfaces\BrandRepositoryInterface;
use Modules\Brand\Infrastructure\Persistence\Eloquent\Models\BrandModel;
use Modules\Core\Infrastructure\Persistence\Repositories\EloquentRepository;
class EloquentBrandRepository extends EloquentRepository implements BrandRepositoryInterface
{
    public function __construct(BrandModel $model) { parent::__construct($model); }
    public function findBySlug(int $tenantId, string $slug): ?Brand { return null; }
    public function findByTenant(int $tenantId): Collection { return new Collection(); }
    public function save(Brand $brand): Brand { return $brand; }
}

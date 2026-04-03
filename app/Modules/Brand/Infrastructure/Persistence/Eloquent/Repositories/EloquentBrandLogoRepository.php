<?php
declare(strict_types=1);
namespace Modules\Brand\Infrastructure\Persistence\Eloquent\Repositories;
use Modules\Brand\Domain\Entities\BrandLogo;
use Modules\Brand\Domain\RepositoryInterfaces\BrandLogoRepositoryInterface;
use Modules\Brand\Infrastructure\Persistence\Eloquent\Models\BrandLogoModel;
use Modules\Core\Infrastructure\Persistence\Repositories\EloquentRepository;
class EloquentBrandLogoRepository extends EloquentRepository implements BrandLogoRepositoryInterface
{
    public function __construct(BrandLogoModel $model) { parent::__construct($model); }
    public function findByUuid(string $uuid): ?BrandLogo { return null; }
    public function findByBrand(int $brandId): ?BrandLogo { return null; }
    public function save(BrandLogo $logo): BrandLogo { return $logo; }
    public function deleteByBrand(int $brandId): bool { return true; }
}

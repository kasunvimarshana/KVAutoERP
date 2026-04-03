<?php
declare(strict_types=1);
namespace Modules\Brand\Application\Services;
use Modules\Brand\Application\Contracts\CreateBrandServiceInterface;
use Modules\Brand\Domain\Entities\Brand;
use Modules\Brand\Domain\RepositoryInterfaces\BrandRepositoryInterface;
use Modules\Core\Application\Services\BaseService;
class CreateBrandService extends BaseService implements CreateBrandServiceInterface
{
    public function __construct(BrandRepositoryInterface $repository) { parent::__construct($repository); }
    protected function handle(array $data): Brand
    {
        $brand = new Brand($data['tenant_id'], $data['name'], $data['slug'], $data['description'] ?? null, $data['website'] ?? null, $data['status'] ?? 'active', $data['attributes'] ?? null, $data['metadata'] ?? null);
        return $this->repository->save($brand);
    }
}

<?php
declare(strict_types=1);
namespace Modules\Brand\Application\Services;
use Modules\Brand\Application\Contracts\UpdateBrandServiceInterface;
use Modules\Brand\Domain\Exceptions\BrandNotFoundException;
use Modules\Brand\Domain\RepositoryInterfaces\BrandRepositoryInterface;
use Modules\Core\Application\Services\BaseService;
class UpdateBrandService extends BaseService implements UpdateBrandServiceInterface
{
    public function __construct(BrandRepositoryInterface $repository) { parent::__construct($repository); }
    protected function handle(array $data): mixed
    {
        $brand = $this->repository->find($data['id']);
        if (!$brand) throw new BrandNotFoundException($data['id']);
        $brand->updateDetails($data['name'] ?? $brand->getName(), $data['slug'] ?? $brand->getSlug(), $data['description'] ?? null, $data['website'] ?? null, $data['attributes'] ?? null, $data['metadata'] ?? null);
        return $this->repository->save($brand);
    }
}

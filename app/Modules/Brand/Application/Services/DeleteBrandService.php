<?php
declare(strict_types=1);
namespace Modules\Brand\Application\Services;
use Modules\Brand\Application\Contracts\DeleteBrandServiceInterface;
use Modules\Brand\Domain\RepositoryInterfaces\BrandRepositoryInterface;
use Modules\Core\Application\Services\BaseService;
class DeleteBrandService extends BaseService implements DeleteBrandServiceInterface
{
    public function __construct(BrandRepositoryInterface $repository) { parent::__construct($repository); }
    protected function handle(array $data): bool { return $this->repository->delete($data['id']); }
}

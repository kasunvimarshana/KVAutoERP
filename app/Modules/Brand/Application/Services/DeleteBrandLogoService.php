<?php
declare(strict_types=1);
namespace Modules\Brand\Application\Services;
use Modules\Brand\Application\Contracts\DeleteBrandLogoServiceInterface;
use Modules\Brand\Domain\RepositoryInterfaces\BrandLogoRepositoryInterface;
use Modules\Core\Application\Services\BaseService;
class DeleteBrandLogoService extends BaseService implements DeleteBrandLogoServiceInterface
{
    public function __construct(BrandLogoRepositoryInterface $repository) { parent::__construct($repository); }
    protected function handle(array $data): bool { return $this->repository->delete($data['id']); }
}

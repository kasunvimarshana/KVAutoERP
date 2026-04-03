<?php
declare(strict_types=1);
namespace Modules\Product\Application\Services;
use Modules\Core\Application\Services\BaseService;
use Modules\Product\Application\Contracts\DeleteProductServiceInterface;
use Modules\Product\Domain\RepositoryInterfaces\ProductRepositoryInterface;
class DeleteProductService extends BaseService implements DeleteProductServiceInterface
{
    public function __construct(ProductRepositoryInterface $repository) { parent::__construct($repository); }
    protected function handle(array $data): bool { return $this->repository->delete($data['id']); }
}

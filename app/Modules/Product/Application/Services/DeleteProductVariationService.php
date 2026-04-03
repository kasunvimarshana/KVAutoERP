<?php
declare(strict_types=1);
namespace Modules\Product\Application\Services;
use Modules\Core\Application\Services\BaseService;
use Modules\Product\Application\Contracts\DeleteProductVariationServiceInterface;
use Modules\Product\Domain\RepositoryInterfaces\ProductVariationRepositoryInterface;
class DeleteProductVariationService extends BaseService implements DeleteProductVariationServiceInterface
{
    public function __construct(ProductVariationRepositoryInterface $repository) { parent::__construct($repository); }
    protected function handle(array $data): bool { return $this->repository->delete($data['id']); }
}

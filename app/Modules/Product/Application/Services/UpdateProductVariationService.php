<?php
declare(strict_types=1);
namespace Modules\Product\Application\Services;
use Modules\Core\Application\Services\BaseService;
use Modules\Product\Application\Contracts\UpdateProductVariationServiceInterface;
use Modules\Product\Domain\RepositoryInterfaces\ProductVariationRepositoryInterface;
class UpdateProductVariationService extends BaseService implements UpdateProductVariationServiceInterface
{
    public function __construct(ProductVariationRepositoryInterface $repository) { parent::__construct($repository); }
    protected function handle(array $data): mixed { return null; }
}

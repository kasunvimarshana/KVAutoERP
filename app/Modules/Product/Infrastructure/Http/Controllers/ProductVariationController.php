<?php
declare(strict_types=1);
namespace Modules\Product\Infrastructure\Http\Controllers;
use Modules\Core\Infrastructure\Http\Controllers\AuthorizedController;
use Modules\Product\Application\Contracts\CreateProductServiceInterface;
use Modules\Product\Application\Contracts\CreateProductVariationServiceInterface;
use Modules\Product\Application\Contracts\DeleteProductVariationServiceInterface;
use Modules\Product\Application\Contracts\FindProductVariationsServiceInterface;
use Modules\Product\Application\Contracts\UpdateProductVariationServiceInterface;
class ProductVariationController extends AuthorizedController
{
    public function __construct(
        private CreateProductVariationServiceInterface $createService,
        private UpdateProductVariationServiceInterface $updateService,
        private DeleteProductVariationServiceInterface $deleteService,
        private CreateProductServiceInterface $createProductService,
        private FindProductVariationsServiceInterface $findService,
    ) {}

    public function index(): void {}
    public function store(): void {}
    public function update(): void {}
    public function destroy(): void {}
}

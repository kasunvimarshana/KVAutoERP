<?php
declare(strict_types=1);
namespace Modules\Product\Infrastructure\Http\Controllers;
use Modules\Core\Infrastructure\Http\Controllers\AuthorizedController;
use Modules\Product\Application\Contracts\CreateComboItemServiceInterface;
use Modules\Product\Application\Contracts\CreateProductServiceInterface;
use Modules\Product\Application\Contracts\DeleteComboItemServiceInterface;
use Modules\Product\Application\Contracts\FindComboItemsServiceInterface;
use Modules\Product\Application\Contracts\UpdateComboItemServiceInterface;
class ProductComboItemController extends AuthorizedController
{
    public function __construct(
        private CreateComboItemServiceInterface $createService,
        private UpdateComboItemServiceInterface $updateService,
        private DeleteComboItemServiceInterface $deleteService,
        private CreateProductServiceInterface $createProductService,
        private FindComboItemsServiceInterface $findService,
    ) {}

    public function index(): void {}
    public function store(): void {}
    public function update(): void {}
    public function destroy(): void {}
}

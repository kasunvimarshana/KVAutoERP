<?php declare(strict_types=1);
namespace Modules\Supplier\Infrastructure\Http\Controllers;
use Modules\Core\Infrastructure\Http\Controllers\AuthorizedController;
use Modules\Supplier\Application\Contracts\CreateSupplierServiceInterface;
use Modules\Supplier\Application\Contracts\DeleteSupplierServiceInterface;
use Modules\Supplier\Application\Contracts\FindSupplierServiceInterface;
use Modules\Supplier\Application\Contracts\UpdateSupplierServiceInterface;
class SupplierController extends AuthorizedController {
    public function __construct(private FindSupplierServiceInterface $find, private CreateSupplierServiceInterface $create, private UpdateSupplierServiceInterface $update, private DeleteSupplierServiceInterface $delete) {}
    public function index(): void {} public function show(): void {} public function store(): void {} public function update(): void {} public function destroy(): void {}
}

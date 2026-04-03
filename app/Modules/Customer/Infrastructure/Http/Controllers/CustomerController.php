<?php declare(strict_types=1);
namespace Modules\Customer\Infrastructure\Http\Controllers;
use Modules\Core\Infrastructure\Http\Controllers\AuthorizedController;
use Modules\Customer\Application\Contracts\CreateCustomerServiceInterface;
use Modules\Customer\Application\Contracts\DeleteCustomerServiceInterface;
use Modules\Customer\Application\Contracts\FindCustomerServiceInterface;
use Modules\Customer\Application\Contracts\UpdateCustomerServiceInterface;
class CustomerController extends AuthorizedController {
    public function __construct(private FindCustomerServiceInterface $find, private CreateCustomerServiceInterface $create, private UpdateCustomerServiceInterface $update, private DeleteCustomerServiceInterface $delete) {}
    public function index(): void {} public function show(): void {} public function store(): void {} public function update(): void {} public function destroy(): void {}
}

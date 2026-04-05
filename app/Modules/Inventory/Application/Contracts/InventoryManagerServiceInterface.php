<?php declare(strict_types=1);
namespace Modules\Inventory\Application\Contracts;
use Modules\Inventory\Domain\Entities\StockMovement;
interface InventoryManagerServiceInterface {
    public function receive(array $data): StockMovement;
    public function issue(array $data): StockMovement;
}

<?php declare(strict_types=1);
namespace Modules\Product\Application\Contracts;
use Modules\Product\Domain\Entities\Product;
interface ProductServiceInterface {
    public function create(array $data): Product;
    public function findById(int $id): ?Product;
    public function update(int $id, array $data): Product;
    public function delete(int $id): void;
}

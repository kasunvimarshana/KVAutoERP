<?php declare(strict_types=1);
namespace Modules\Tax\Domain\RepositoryInterfaces;
use Modules\Tax\Domain\Entities\TaxGroup;
use Modules\Tax\Domain\Entities\TaxGroupRate;
interface TaxGroupRepositoryInterface {
    public function findById(int $id): ?TaxGroup;
    public function findRatesByGroup(int $taxGroupId): array;
    public function save(TaxGroup $group): TaxGroup;
    public function saveRate(TaxGroupRate $rate): TaxGroupRate;
    public function delete(int $id): void;
}

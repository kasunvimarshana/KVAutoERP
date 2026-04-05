<?php declare(strict_types=1);
namespace Modules\Inventory\Domain\RepositoryInterfaces;
use Modules\Inventory\Domain\Entities\CycleCount;
use Modules\Inventory\Domain\Entities\CycleCountLine;
interface CycleCountRepositoryInterface {
    public function findById(int $id): ?CycleCount;
    public function save(CycleCount $count): CycleCount;
    public function saveLine(CycleCountLine $line): CycleCountLine;
    public function findLinesByCount(int $cycleCountId): array;
}

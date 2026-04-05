<?php

declare(strict_types=1);

namespace Modules\Order\Domain\RepositoryInterfaces;

use Modules\Order\Domain\Entities\ReturnLine;

interface ReturnLineRepositoryInterface
{
    /** @return ReturnLine[] */
    public function findByReturn(int $returnId): array;
    public function create(array $data): ReturnLine;
    public function update(int $id, array $data): ?ReturnLine;
    /** @return ReturnLine[] */
    public function bulkCreate(array $lines): array;
}

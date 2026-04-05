<?php

declare(strict_types=1);

namespace Modules\Tax\Domain\RepositoryInterfaces;

use Modules\Tax\Domain\Entities\TaxGroup;

interface TaxGroupRepositoryInterface
{
    public function create(array $data): TaxGroup;

    public function update(int $id, array $data): TaxGroup;

    public function delete(int $id): void;

    public function findById(int $id, int $tenantId): ?TaxGroup;

    public function findByCode(string $code, int $tenantId): ?TaxGroup;

    /** @return TaxGroup[] */
    public function listAll(int $tenantId): array;
}

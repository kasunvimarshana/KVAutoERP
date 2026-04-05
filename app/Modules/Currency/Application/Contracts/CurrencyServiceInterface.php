<?php

declare(strict_types=1);

namespace Modules\Currency\Application\Contracts;

use Modules\Currency\Domain\Entities\Currency;

interface CurrencyServiceInterface
{
    public function create(array $data): Currency;

    public function update(int $id, array $data): Currency;

    public function findById(int $id, int $tenantId): ?Currency;

    public function findByCode(string $code, int $tenantId): ?Currency;

    /** @return Currency[] */
    public function listAll(int $tenantId): array;

    public function setDefault(int $id, int $tenantId): Currency;
}

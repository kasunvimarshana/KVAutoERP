<?php

declare(strict_types=1);

namespace Modules\Currency\Application\Contracts;

use Modules\Currency\Domain\Entities\Currency;

interface CurrencyServiceInterface
{
    public function findById(int $id): Currency;

    public function findByCode(int $tenantId, string $code): Currency;

    public function findDefault(int $tenantId): Currency;

    /** @return Currency[] */
    public function all(int $tenantId): array;

    public function create(array $data): Currency;

    public function update(int $id, array $data): Currency;

    public function delete(int $id): bool;

    public function setDefault(int $tenantId, string $code): Currency;
}

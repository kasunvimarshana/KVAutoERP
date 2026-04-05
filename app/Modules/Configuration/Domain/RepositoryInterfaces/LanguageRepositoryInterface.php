<?php

declare(strict_types=1);

namespace Modules\Configuration\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\Configuration\Domain\Entities\Language;

interface LanguageRepositoryInterface
{
    public function findById(int $id): ?Language;

    public function findByCode(?int $tenantId, string $code): ?Language;

    public function findDefault(?int $tenantId): ?Language;

    /** @return Collection<int, Language> */
    public function findActive(?int $tenantId): Collection;

    public function create(array $data): Language;

    public function update(int $id, array $data): ?Language;

    public function delete(int $id): bool;
}

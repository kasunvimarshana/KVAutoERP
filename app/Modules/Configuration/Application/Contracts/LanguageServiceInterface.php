<?php

declare(strict_types=1);

namespace Modules\Configuration\Application\Contracts;

use Illuminate\Support\Collection;
use Modules\Configuration\Domain\Entities\Language;

interface LanguageServiceInterface
{
    public function findById(int $id): ?Language;

    public function findByCode(?int $tenantId, string $code): ?Language;

    public function getDefault(?int $tenantId): ?Language;

    /** @return Collection<int, Language> */
    public function getActive(?int $tenantId): Collection;

    public function create(array $data): Language;

    public function update(int $id, array $data): ?Language;

    public function delete(int $id): bool;

    public function setDefault(?int $tenantId, int $languageId): Language;
}

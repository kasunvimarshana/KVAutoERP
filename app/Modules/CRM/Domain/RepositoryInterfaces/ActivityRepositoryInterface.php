<?php

declare(strict_types=1);

namespace Modules\CRM\Domain\RepositoryInterfaces;

use Modules\CRM\Domain\Entities\Activity;

interface ActivityRepositoryInterface
{
    public function findById(int $id): ?Activity;

    /** @return Activity[] */
    public function findByRelated(string $relatedType, int $relatedId): array;

    /** @return Activity[] */
    public function findPending(int $tenantId): array;

    public function create(array $data): Activity;

    public function update(int $id, array $data): ?Activity;

    public function delete(int $id): bool;
}

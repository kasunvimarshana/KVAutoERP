<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\RepositoryInterfaces;

use Modules\Accounting\Domain\Entities\JournalEntry;

interface JournalEntryRepositoryInterface
{
    public function findById(int $id): ?JournalEntry;

    public function findByReference(int $tenantId, string $referenceNo): ?JournalEntry;

    public function create(array $data, array $lines): JournalEntry;

    public function update(int $id, array $data): ?JournalEntry;

    /** @return JournalEntry[] */
    public function findByDateRange(int $tenantId, \DateTimeInterface $from, \DateTimeInterface $to): array;

    public function post(int $id): bool;

    public function reverse(int $id): JournalEntry;
}

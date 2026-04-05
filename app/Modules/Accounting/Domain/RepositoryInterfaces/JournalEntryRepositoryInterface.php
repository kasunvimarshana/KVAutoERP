<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\Accounting\Domain\Entities\JournalEntry;
use Modules\Accounting\Domain\Entities\JournalLine;

interface JournalEntryRepositoryInterface
{
    public function findById(int $id): ?JournalEntry;

    public function findByReference(int $tenantId, string $referenceNo): ?JournalEntry;

    /** @return Collection<int, JournalEntry> */
    public function findByDateRange(int $tenantId, string $startDate, string $endDate): Collection;

    /** @return Collection<int, JournalEntry> */
    public function findByStatus(int $tenantId, string $status): Collection;

    public function create(array $data): JournalEntry;

    public function update(int $id, array $data): ?JournalEntry;

    public function delete(int $id): bool;

    public function addLine(int $journalEntryId, array $lineData): JournalLine;

    /** @return Collection<int, JournalLine> */
    public function getLines(int $journalEntryId): Collection;
}

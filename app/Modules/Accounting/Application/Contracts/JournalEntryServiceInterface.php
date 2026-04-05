<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Contracts;

use Illuminate\Support\Collection;
use Modules\Accounting\Domain\Entities\JournalEntry;
use Modules\Accounting\Domain\Entities\JournalLine;

interface JournalEntryServiceInterface
{
    /**
     * @param array<int, array{account_id: int, description: ?string, debit: float, credit: float}> $lines
     */
    public function createEntry(
        int $tenantId,
        string $referenceNo,
        string $date,
        string $description,
        string $type,
        array $lines,
        ?int $createdBy = null,
    ): JournalEntry;

    public function postEntry(int $entryId): JournalEntry;

    public function voidEntry(int $entryId, string $reason): JournalEntry;

    public function getEntry(int $entryId): JournalEntry;

    /** @return Collection<int, JournalLine> */
    public function getLines(int $entryId): Collection;
}

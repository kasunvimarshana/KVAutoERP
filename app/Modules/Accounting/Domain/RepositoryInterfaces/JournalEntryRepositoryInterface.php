<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\Accounting\Domain\Entities\JournalEntry;
use Modules\Accounting\Domain\Entities\JournalLine;

interface JournalEntryRepositoryInterface
{
    public function findById(string $id): ?JournalEntry;
    public function allByTenant(string $tenantId): Collection;
    public function create(array $data, array $lines): JournalEntry;
    public function update(string $id, array $data): JournalEntry;
    public function updateStatus(string $id, string $status): JournalEntry;
    public function delete(string $id): bool;
    public function getLines(string $journalEntryId): Collection;
    public function nextEntryNumber(string $tenantId): string;
}

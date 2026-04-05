<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Contracts;

use Illuminate\Support\Collection;
use Modules\Accounting\Domain\Entities\JournalEntry;

interface JournalEntryServiceInterface
{
    public function createEntry(array $data, array $lines): JournalEntry;
    public function postEntry(string $id): JournalEntry;
    public function voidEntry(string $id): JournalEntry;
    public function getEntry(string $id): JournalEntry;
    public function getAll(string $tenantId): Collection;
}

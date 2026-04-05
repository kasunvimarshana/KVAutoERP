<?php declare(strict_types=1);
namespace Modules\Accounting\Domain\RepositoryInterfaces;
use Modules\Accounting\Domain\Entities\JournalEntry;
use Modules\Accounting\Domain\Entities\JournalLine;
interface JournalEntryRepositoryInterface {
    public function findById(int $id): ?JournalEntry;
    public function save(JournalEntry $entry): JournalEntry;
    public function saveLine(JournalLine $line): JournalLine;
    public function findLinesByEntry(int $entryId): array;
    public function delete(int $id): void;
}

<?php declare(strict_types=1);
namespace Modules\Accounting\Application\Services;
use Modules\Accounting\Domain\Entities\JournalEntry;
use Modules\Accounting\Domain\Entities\JournalLine;
use Modules\Accounting\Domain\RepositoryInterfaces\JournalEntryRepositoryInterface;
class CreateJournalEntryService {
    public function __construct(private readonly JournalEntryRepositoryInterface $repo) {}
    public function create(array $data): JournalEntry {
        $lines = [];
        foreach ($data['lines'] as $line) {
            $lines[] = new JournalLine(null, 0, $line['account_id'], (float)($line['debit'] ?? 0.0), (float)($line['credit'] ?? 0.0), $line['description'] ?? null);
        }
        $entry = new JournalEntry(null, $data['tenant_id'], $data['reference'], $data['description'], new \DateTimeImmutable($data['date']), 'draft', $data['currency'] ?? 'USD', $data['created_by'] ?? null, null, $lines);
        if (!$entry->isBalanced()) throw new \DomainException("Journal entry is not balanced: debit={$entry->getTotalDebit()}, credit={$entry->getTotalCredit()}");
        $saved = $this->repo->save($entry);
        foreach ($lines as $i => $line) {
            $newLine = new JournalLine(null, $saved->getId(), $line->getAccountId(), $line->getDebitAmount(), $line->getCreditAmount(), $line->getDescription());
            $savedLine = $this->repo->saveLine($newLine);
            $lines[$i] = $savedLine;
        }
        $saved->setLines($lines);
        return $saved;
    }
}

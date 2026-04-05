<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Services;

use Illuminate\Support\Collection;
use Modules\Accounting\Application\Contracts\JournalEntryServiceInterface;
use Modules\Accounting\Domain\Entities\JournalEntry;
use Modules\Accounting\Domain\RepositoryInterfaces\JournalEntryRepositoryInterface;
use Modules\Core\Domain\Exceptions\DomainException;
use Modules\Core\Domain\Exceptions\NotFoundException;

class JournalEntryService implements JournalEntryServiceInterface
{
    public function __construct(
        private readonly JournalEntryRepositoryInterface $repository,
    ) {}

    public function createEntry(array $data, array $lines): JournalEntry
    {
        $this->validateLines($lines);

        $totalDebit  = array_sum(array_column($lines, 'debit'));
        $totalCredit = array_sum(array_column($lines, 'credit'));

        if (abs($totalDebit - $totalCredit) > 0.0001) {
            throw new DomainException(
                "Journal entry is not balanced. Debit: {$totalDebit}, Credit: {$totalCredit}"
            );
        }

        $data['entry_number'] = $this->repository->nextEntryNumber($data['tenant_id']);
        $data['total_debit']  = $totalDebit;
        $data['total_credit'] = $totalCredit;
        $data['status']       = $data['status'] ?? 'draft';

        return $this->repository->create($data, $lines);
    }

    public function postEntry(string $id): JournalEntry
    {
        $entry = $this->getEntry($id);

        if (! $entry->isDraft()) {
            throw new DomainException("Only draft entries can be posted. Current status: {$entry->getStatus()}");
        }

        if (! $entry->isBalanced()) {
            throw new DomainException('Cannot post an unbalanced journal entry.');
        }

        return $this->repository->updateStatus($id, 'posted');
    }

    public function voidEntry(string $id): JournalEntry
    {
        $entry = $this->getEntry($id);

        if ($entry->isVoided()) {
            throw new DomainException('Journal entry is already voided.');
        }

        return $this->repository->updateStatus($id, 'voided');
    }

    public function getEntry(string $id): JournalEntry
    {
        $entry = $this->repository->findById($id);
        if (! $entry) {
            throw new NotFoundException('JournalEntry', $id);
        }
        return $entry;
    }

    public function getAll(string $tenantId): Collection
    {
        return $this->repository->allByTenant($tenantId);
    }

    private function validateLines(array $lines): void
    {
        if (count($lines) < 2) {
            throw new DomainException('A journal entry must have at least 2 lines.');
        }

        foreach ($lines as $line) {
            $debit  = (float)($line['debit'] ?? 0);
            $credit = (float)($line['credit'] ?? 0);

            if ($debit < 0 || $credit < 0) {
                throw new DomainException('Debit and credit amounts must be non-negative.');
            }

            if ($debit > 0 && $credit > 0) {
                throw new DomainException('A line cannot have both debit and credit amounts.');
            }
        }
    }
}

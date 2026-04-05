<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Services;

use Illuminate\Support\Collection;
use Modules\Accounting\Application\Contracts\JournalEntryServiceInterface;
use Modules\Accounting\Domain\Entities\JournalEntry;
use Modules\Accounting\Domain\Entities\JournalLine;
use Modules\Accounting\Domain\Exceptions\JournalEntryAlreadyPostedException;
use Modules\Accounting\Domain\Exceptions\JournalEntryNotVoidableException;
use Modules\Accounting\Domain\Exceptions\UnbalancedJournalEntryException;
use Modules\Accounting\Domain\RepositoryInterfaces\JournalEntryRepositoryInterface;
use Modules\Core\Domain\Exceptions\NotFoundException;

final class JournalEntryService implements JournalEntryServiceInterface
{
    public function __construct(
        private readonly JournalEntryRepositoryInterface $repository,
    ) {}

    public function createEntry(
        int $tenantId,
        string $referenceNo,
        string $date,
        string $description,
        string $type,
        array $lines,
        ?int $createdBy = null,
    ): JournalEntry {
        $this->assertBalanced($lines);

        $entry = $this->repository->create([
            'tenant_id'    => $tenantId,
            'reference_no' => $referenceNo,
            'date'         => $date,
            'description'  => $description,
            'status'       => JournalEntry::STATUS_DRAFT,
            'type'         => $type,
            'created_by'   => $createdBy,
        ]);

        foreach ($lines as $line) {
            $this->repository->addLine($entry->id, $line);
        }

        return $entry;
    }

    public function postEntry(int $entryId): JournalEntry
    {
        $entry = $this->getEntry($entryId);

        if ($entry->isPosted()) {
            throw new JournalEntryAlreadyPostedException($entryId);
        }

        $lines = $this->repository->getLines($entryId);
        $this->assertBalancedLines($lines);

        return $this->repository->update($entryId, [
            'status'    => JournalEntry::STATUS_POSTED,
            'posted_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);
    }

    public function voidEntry(int $entryId, string $reason): JournalEntry
    {
        $entry = $this->getEntry($entryId);

        if (!$entry->isVoidable()) {
            throw new JournalEntryNotVoidableException($entryId, $entry->status);
        }

        return $this->repository->update($entryId, [
            'status'      => JournalEntry::STATUS_VOIDED,
            'voided_at'   => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            'void_reason' => $reason,
        ]);
    }

    public function getEntry(int $entryId): JournalEntry
    {
        $entry = $this->repository->findById($entryId);

        if ($entry === null) {
            throw new NotFoundException("Journal entry #{$entryId} not found.");
        }

        return $entry;
    }

    public function getLines(int $entryId): Collection
    {
        return $this->repository->getLines($entryId);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * @param array<int, array{debit: float, credit: float}> $lines
     */
    private function assertBalanced(array $lines): void
    {
        $totalDebits  = 0.0;
        $totalCredits = 0.0;

        foreach ($lines as $line) {
            $totalDebits  += (float) ($line['debit']  ?? 0.0);
            $totalCredits += (float) ($line['credit'] ?? 0.0);
        }

        if (abs($totalDebits - $totalCredits) > 0.000001) {
            throw new UnbalancedJournalEntryException($totalDebits, $totalCredits);
        }
    }

    /** @param Collection<int, JournalLine> $lines */
    private function assertBalancedLines(Collection $lines): void
    {
        $totalDebits  = 0.0;
        $totalCredits = 0.0;

        foreach ($lines as $line) {
            $totalDebits  += $line->debit;
            $totalCredits += $line->credit;
        }

        if (abs($totalDebits - $totalCredits) > 0.000001) {
            throw new UnbalancedJournalEntryException($totalDebits, $totalCredits);
        }
    }
}

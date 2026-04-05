<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\Entities;

use Modules\Accounting\Domain\Exceptions\InvalidJournalLineException;

final class JournalLine
{
    public function __construct(
        public readonly int $id,
        public readonly int $journalEntryId,
        public readonly int $accountId,
        public readonly ?string $description,
        public readonly float $debit,
        public readonly float $credit,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {}

    public function getAmount(): float
    {
        return $this->debit - $this->credit;
    }

    /**
     * @throws InvalidJournalLineException
     */
    public function validate(): void
    {
        if ($this->debit > 0 && $this->credit > 0) {
            throw new InvalidJournalLineException(
                'A journal line cannot have both a debit and a credit amount.'
            );
        }

        if ($this->debit === 0.0 && $this->credit === 0.0) {
            throw new InvalidJournalLineException(
                'A journal line must have either a debit or a credit amount.'
            );
        }
    }
}

<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\Exceptions;

use Modules\Core\Domain\Exceptions\DomainException;

final class UnbalancedJournalEntryException extends DomainException
{
    public function __construct(float $totalDebits, float $totalCredits)
    {
        parent::__construct(
            sprintf(
                'Journal entry is unbalanced: total debits (%.6f) do not equal total credits (%.6f).',
                $totalDebits,
                $totalCredits
            )
        );
    }
}

<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\Exceptions;

use Modules\Core\Domain\Exceptions\DomainException;

final class JournalEntryNotVoidableException extends DomainException
{
    public function __construct(int $entryId, string $status)
    {
        parent::__construct(
            "Journal entry #{$entryId} cannot be voided because its status is '{$status}'."
        );
    }
}

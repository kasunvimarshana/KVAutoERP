<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\Exceptions;

use Modules\Core\Domain\Exceptions\DomainException;

final class JournalEntryAlreadyPostedException extends DomainException
{
    public function __construct(int $entryId)
    {
        parent::__construct("Journal entry #{$entryId} is already posted.");
    }
}

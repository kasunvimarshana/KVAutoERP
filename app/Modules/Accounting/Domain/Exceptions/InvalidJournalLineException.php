<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\Exceptions;

use Modules\Core\Domain\Exceptions\DomainException;

final class InvalidJournalLineException extends DomainException {}

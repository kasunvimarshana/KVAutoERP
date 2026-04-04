<?php

declare(strict_types=1);

namespace Modules\Configuration\Domain\Exceptions;

use RuntimeException;

class CircularHierarchyException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Moving this org unit would create a circular hierarchy.', 422);
    }
}

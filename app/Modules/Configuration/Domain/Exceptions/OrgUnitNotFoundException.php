<?php

declare(strict_types=1);

namespace Modules\Configuration\Domain\Exceptions;

use Modules\Core\Domain\Exceptions\DomainException;

class OrgUnitNotFoundException extends DomainException
{
    public function __construct(int $id)
    {
        parent::__construct("OrgUnit with id '{$id}' not found", 404);
    }
}

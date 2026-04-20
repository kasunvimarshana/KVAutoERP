<?php

declare(strict_types=1);

namespace Modules\Purchase\Domain\Exceptions;

class GrnNotFoundException extends \Modules\Core\Domain\Exceptions\NotFoundException
{
    public function __construct(mixed $id = null)
    {
        parent::__construct('GrnHeader', $id);
    }
}

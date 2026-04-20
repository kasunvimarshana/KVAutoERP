<?php

declare(strict_types=1);

namespace Modules\Purchase\Domain\Exceptions;

class PurchaseOrderNotFoundException extends \Modules\Core\Domain\Exceptions\NotFoundException
{
    public function __construct(mixed $id = null)
    {
        parent::__construct('PurchaseOrder', $id);
    }
}

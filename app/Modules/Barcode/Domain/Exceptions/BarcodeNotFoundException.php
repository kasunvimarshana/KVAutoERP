<?php

declare(strict_types=1);

namespace Modules\Barcode\Domain\Exceptions;

use RuntimeException;

class BarcodeNotFoundException extends RuntimeException
{
    public function __construct(int $id)
    {
        parent::__construct("Barcode definition [{$id}] not found.", 404);
    }

    public static function withId(int $id): self
    {
        return new self($id);
    }
}

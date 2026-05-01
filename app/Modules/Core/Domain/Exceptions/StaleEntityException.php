<?php

declare(strict_types=1);

namespace Modules\Core\Domain\Exceptions;

class StaleEntityException extends DomainException
{
    public function __construct(int|string $id, int $expectedVersion, int $actualVersion)
    {
        parent::__construct(
            sprintf(
                'Optimistic lock conflict: entity #%s was updated by another process (expected row_version %d, found %d).',
                $id,
                $expectedVersion,
                $actualVersion
            )
        );
    }
}

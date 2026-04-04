<?php

declare(strict_types=1);

namespace Modules\Configuration\Domain\Exceptions;

use Modules\Core\Domain\Exceptions\DomainException;

class SettingNotFoundException extends DomainException
{
    public function __construct(string $group, string $key)
    {
        parent::__construct("Setting '{$group}.{$key}' not found", 404);
    }
}

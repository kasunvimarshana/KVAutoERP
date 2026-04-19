<?php

declare(strict_types=1);

namespace Modules\Core\Infrastructure\Persistence\Eloquent\Traits;

use Modules\Audit\Infrastructure\Persistence\Eloquent\Traits\HasAudit as AuditHasAudit;

trait HasAudit
{
    use AuditHasAudit;
}

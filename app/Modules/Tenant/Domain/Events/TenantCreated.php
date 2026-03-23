<?php

namespace Modules\Tenant\Domain\Events;

use Modules\Tenant\Domain\Entities\Tenant;

class TenantCreated
{
    public function __construct(public Tenant $tenant) {}
}

<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Events;

use App\Domain\Tenant\Entities\Tenant;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class TenantConfigUpdated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Tenant $tenant,
        public readonly array  $changedKeys,
    ) {}
}

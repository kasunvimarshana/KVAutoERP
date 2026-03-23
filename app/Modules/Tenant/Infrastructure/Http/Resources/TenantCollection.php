<?php

namespace Modules\Tenant\Infrastructure\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class TenantCollection extends ResourceCollection
{
    public $collects = TenantResource::class;
}

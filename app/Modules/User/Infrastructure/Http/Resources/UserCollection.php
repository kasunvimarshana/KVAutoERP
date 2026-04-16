<?php

declare(strict_types=1);

namespace Modules\User\Infrastructure\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    public string $collects = UserResource::class;
}

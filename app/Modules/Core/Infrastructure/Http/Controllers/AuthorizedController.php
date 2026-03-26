<?php

declare(strict_types=1);

namespace Modules\Core\Infrastructure\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller;

class AuthorizedController extends Controller
{
    use AuthorizesRequests {
        authorize as protected laravelAuthorize;
    }

    public function authorize($ability, $arguments = [])
    {
        // return $this->laravelAuthorize($ability, $arguments);
        return true;
    }
}

<?php

declare(strict_types=1);

namespace App\Core\Exceptions;

use RuntimeException;

/** Thrown when a tenant cannot be resolved or is inactive. */
class TenantException extends RuntimeException {}

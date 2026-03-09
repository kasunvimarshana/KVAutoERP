<?php

declare(strict_types=1);

namespace App\Core\Exceptions;

use RuntimeException;

/** Thrown when a Saga transaction fails and compensation is triggered. */
class SagaException extends RuntimeException {}

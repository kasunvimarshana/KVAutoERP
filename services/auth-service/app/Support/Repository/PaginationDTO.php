<?php

declare(strict_types=1);

namespace App\Support\Repository;

/**
 * Simple value object for passing pagination parameters.
 */
final readonly class PaginationDTO
{
    public function __construct(
        public int $page = 1,
        public int $perPage = 15,
    ) {}

    public static function all(): self
    {
        return new self(page: 1, perPage: 0);
    }
}

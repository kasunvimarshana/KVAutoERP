<?php

declare(strict_types=1);

namespace App\Application\Shared\DTOs;

final readonly class PaginationDTO
{
    public function __construct(
        public int    $page      = 1,
        public int    $perPage   = 15,
        public string $sortBy    = 'created_at',
        public string $sortDir   = 'desc',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            page:    max(1, (int) ($data['page'] ?? 1)),
            perPage: min(100, max(1, (int) ($data['per_page'] ?? 15))),
            sortBy:  $data['sort_by'] ?? 'created_at',
            sortDir: in_array($data['sort_dir'] ?? 'desc', ['asc', 'desc'], true)
                        ? ($data['sort_dir'] ?? 'desc')
                        : 'desc',
        );
    }
}

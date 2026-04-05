<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Contracts;

use Modules\Accounting\Domain\Entities\JournalEntry;

interface JournalEntryServiceInterface
{
    public function create(array $data): JournalEntry;

    public function post(int $id): void;

    public function reverse(int $id): JournalEntry;
}

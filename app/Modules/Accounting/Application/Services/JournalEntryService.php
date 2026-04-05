<?php

declare(strict_types=1);

namespace Modules\Accounting\Application\Services;

use Modules\Accounting\Application\Contracts\JournalEntryServiceInterface;
use Modules\Accounting\Domain\Entities\JournalEntry;
use Modules\Accounting\Domain\RepositoryInterfaces\JournalEntryRepositoryInterface;
use Modules\Core\Domain\Exceptions\DomainException;
use Modules\Core\Domain\Exceptions\NotFoundException;

class JournalEntryService implements JournalEntryServiceInterface
{
    public function __construct(
        private readonly JournalEntryRepositoryInterface $repository,
    ) {}

    public function create(array $data): JournalEntry
    {
        $lines = $data['lines'] ?? [];

        $totalDebits = array_sum(array_column($lines, 'debit'));
        $totalCredits = array_sum(array_column($lines, 'credit'));

        if (abs($totalDebits - $totalCredits) >= 0.0001) {
            throw new DomainException(
                sprintf(
                    'Journal entry is not balanced: debits %.4f != credits %.4f',
                    $totalDebits,
                    $totalCredits,
                )
            );
        }

        return $this->repository->create($data, $lines);
    }

    public function post(int $id): void
    {
        $entry = $this->repository->findById($id);

        if ($entry === null) {
            throw new NotFoundException('JournalEntry', $id);
        }

        $this->repository->post($id);
    }

    public function reverse(int $id): JournalEntry
    {
        $entry = $this->repository->findById($id);

        if ($entry === null) {
            throw new NotFoundException('JournalEntry', $id);
        }

        return $this->repository->reverse($id);
    }
}

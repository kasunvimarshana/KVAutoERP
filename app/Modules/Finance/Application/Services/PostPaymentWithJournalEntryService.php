<?php

declare(strict_types=1);

namespace Modules\Finance\Application\Services;

use Illuminate\Support\Facades\DB;
use Modules\Core\Application\Contracts\ServiceInterface;
use Modules\Finance\Application\Contracts\CreateJournalEntryServiceInterface;
use Modules\Finance\Application\Contracts\PostPaymentServiceInterface;
use Modules\Finance\Application\Contracts\PostPaymentWithJournalEntryServiceInterface;
use Modules\Finance\Domain\Entities\JournalEntry;
use Modules\Finance\Domain\Entities\Payment;

/**
 * Atomically creates a Journal Entry and posts the associated Payment.
 *
 * Both operations are wrapped in a single DB transaction so a failure in
 * either step rolls back the other — preventing posted payments from existing
 * without a corresponding accounting entry.
 */
class PostPaymentWithJournalEntryService implements PostPaymentWithJournalEntryServiceInterface
{
    public function __construct(
        private readonly CreateJournalEntryServiceInterface $createJournalEntryService,
        private readonly PostPaymentServiceInterface $postPaymentService,
    ) {}

    /**
     * @param  array{payment_id: int, journal_entry: array<string, mixed>}  $data
     * @return array{journal_entry: JournalEntry, payment: Payment}
     */
    public function execute(array $data = []): array
    {
        return DB::transaction(function () use ($data) {
            /** @var JournalEntry $journalEntry */
            $journalEntry = $this->createJournalEntryService->execute($data['journal_entry']);

            /** @var Payment $payment */
            $payment = $this->postPaymentService->execute([
                'id'               => (int) ($data['payment_id'] ?? 0),
                'journal_entry_id' => $journalEntry->getId(),
            ]);

            return [
                'journal_entry' => $journalEntry,
                'payment'       => $payment,
            ];
        });
    }

    // Read-side methods delegated to the underlying post-payment service to satisfy ServiceInterface.

    public function find(mixed $id): mixed
    {
        return $this->postPaymentService->find($id);
    }

    public function list(
        array $filters = [],
        ?int $perPage = null,
        int $page = 1,
        ?string $sort = null,
        ?string $include = null
    ): mixed {
        return $this->postPaymentService->list($filters, $perPage, $page, $sort, $include);
    }
}

<?php

declare(strict_types=1);

namespace Modules\Finance\Infrastructure\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\Finance\Application\Contracts\CreateJournalEntryServiceInterface;
use Modules\Finance\Domain\RepositoryInterfaces\FiscalPeriodRepositoryInterface;
use Modules\Purchase\Domain\Events\PurchaseInvoiceApproved;

class HandlePurchaseInvoiceApproved
{
    public function __construct(
        private readonly FiscalPeriodRepositoryInterface $fiscalPeriodRepository,
        private readonly CreateJournalEntryServiceInterface $createJournalEntryService,
    ) {}

    public function handle(PurchaseInvoiceApproved $event): void
    {
        if ($event->apAccountId === null) {
            Log::warning('HandlePurchaseInvoiceApproved: AP account not configured; skipping journal entry', [
                'purchase_invoice_id' => $event->purchaseInvoiceId,
                'tenant_id' => $event->tenantId,
            ]);

            return;
        }

        $invoiceDate = $event->invoiceDate !== ''
            ? new \DateTimeImmutable($event->invoiceDate)
            : new \DateTimeImmutable;

        $period = $this->fiscalPeriodRepository->findOpenPeriodForDate($event->tenantId, $invoiceDate);
        if ($period === null) {
            Log::warning('HandlePurchaseInvoiceApproved: no open fiscal period for invoice date; skipping journal entry', [
                'purchase_invoice_id' => $event->purchaseInvoiceId,
                'invoice_date' => $event->invoiceDate,
                'tenant_id' => $event->tenantId,
            ]);

            return;
        }

        // A balanced AP journal entry requires both the AP (credit) account and a
        // corresponding debit (inventory/expense) account. The debit account is not
        // carried on the event because it varies per invoice line. Until line-level
        // account data is propagated in the event, we defer journal creation and log.
        Log::info('HandlePurchaseInvoiceApproved: AP journal entry deferred; debit (inventory/expense) account not available in event', [
            'purchase_invoice_id' => $event->purchaseInvoiceId,
            'ap_account_id' => $event->apAccountId,
            'grand_total' => $event->grandTotal,
            'fiscal_period_id' => $period->getId(),
            'tenant_id' => $event->tenantId,
        ]);
    }
}

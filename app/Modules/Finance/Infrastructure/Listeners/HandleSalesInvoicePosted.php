<?php

declare(strict_types=1);

namespace Modules\Finance\Infrastructure\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\Finance\Application\Contracts\CreateJournalEntryServiceInterface;
use Modules\Finance\Domain\RepositoryInterfaces\FiscalPeriodRepositoryInterface;
use Modules\Sales\Domain\Events\SalesInvoicePosted;

class HandleSalesInvoicePosted
{
    public function __construct(
        private readonly FiscalPeriodRepositoryInterface $fiscalPeriodRepository,
        private readonly CreateJournalEntryServiceInterface $createJournalEntryService,
    ) {}

    public function handle(SalesInvoicePosted $event): void
    {
        if ($event->arAccountId === null) {
            Log::warning('HandleSalesInvoicePosted: AR account not configured; skipping journal entry', [
                'sales_invoice_id' => $event->salesInvoiceId,
                'tenant_id' => $event->tenantId,
            ]);

            return;
        }

        $invoiceDate = $event->invoiceDate !== ''
            ? new \DateTimeImmutable($event->invoiceDate)
            : new \DateTimeImmutable;

        $period = $this->fiscalPeriodRepository->findOpenPeriodForDate($event->tenantId, $invoiceDate);
        if ($period === null) {
            Log::warning('HandleSalesInvoicePosted: no open fiscal period for invoice date; skipping journal entry', [
                'sales_invoice_id' => $event->salesInvoiceId,
                'invoice_date' => $event->invoiceDate,
                'tenant_id' => $event->tenantId,
            ]);

            return;
        }

        // A balanced AR journal entry requires both the AR (debit) account and a
        // corresponding credit (revenue) account. The revenue account is not carried
        // on the event because it varies per invoice line. Until line-level account
        // data is propagated in the event, we defer journal creation and log.
        Log::info('HandleSalesInvoicePosted: AR journal entry deferred; credit (revenue) account not available in event', [
            'sales_invoice_id' => $event->salesInvoiceId,
            'ar_account_id' => $event->arAccountId,
            'grand_total' => $event->grandTotal,
            'fiscal_period_id' => $period->getId(),
            'tenant_id' => $event->tenantId,
        ]);
    }
}

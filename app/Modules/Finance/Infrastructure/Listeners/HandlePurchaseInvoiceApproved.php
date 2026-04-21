<?php

declare(strict_types=1);

namespace Modules\Finance\Infrastructure\Listeners;

use Illuminate\Support\Facades\DB;
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

        if (empty($event->lines)) {
            Log::warning('HandlePurchaseInvoiceApproved: no invoice lines in event; skipping journal entry', [
                'purchase_invoice_id' => $event->purchaseInvoiceId,
                'tenant_id' => $event->tenantId,
            ]);

            return;
        }

        // Aggregate debit amounts by account_id (inventory / expense accounts)
        $debitsByAccount = [];
        foreach ($event->lines as $line) {
            $accountId = isset($line['account_id']) ? (int) $line['account_id'] : null;
            if ($accountId === null) {
                Log::warning('HandlePurchaseInvoiceApproved: invoice line missing account_id; skipping journal entry', [
                    'purchase_invoice_id' => $event->purchaseInvoiceId,
                    'line' => $line,
                ]);

                return;
            }

            $amount = (float) ($line['line_total'] ?? '0') + (float) ($line['tax_amount'] ?? '0');
            $debitsByAccount[$accountId] = ($debitsByAccount[$accountId] ?? 0.0) + $amount;
        }

        $grandTotal = (float) $event->grandTotal;
        $debitTotal = (float) array_sum($debitsByAccount);

        if (abs($debitTotal - $grandTotal) > 0.01) {
            Log::warning('HandlePurchaseInvoiceApproved: debit total does not match grand total; skipping journal entry', [
                'purchase_invoice_id' => $event->purchaseInvoiceId,
                'debit_total' => $debitTotal,
                'grand_total' => $grandTotal,
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

        $exchangeRate = (float) $event->exchangeRate;
        $description = 'AP entry for Purchase Invoice #'.$event->purchaseInvoiceId;

        $jeLines = [];
        foreach ($debitsByAccount as $accountId => $amount) {
            $jeLines[] = [
                'account_id' => $accountId,
                'debit_amount' => $amount,
                'credit_amount' => 0.0,
                'description' => $description,
                'currency_id' => $event->currencyId,
                'exchange_rate' => $exchangeRate,
                'base_debit_amount' => $amount * $exchangeRate,
                'base_credit_amount' => 0.0,
            ];
        }

        $jeLines[] = [
            'account_id' => $event->apAccountId,
            'debit_amount' => 0.0,
            'credit_amount' => $grandTotal,
            'description' => $description,
            'currency_id' => $event->currencyId,
            'exchange_rate' => $exchangeRate,
            'base_debit_amount' => 0.0,
            'base_credit_amount' => $grandTotal * $exchangeRate,
        ];

        DB::transaction(function () use ($event, $period, $invoiceDate, $description, $jeLines): void {
            $this->createJournalEntryService->execute([
                'tenant_id' => $event->tenantId,
                'fiscal_period_id' => $period->getId(),
                'entry_date' => $invoiceDate->format('Y-m-d'),
                'created_by' => 1,
                'entry_type' => 'system',
                'reference_type' => 'purchase_invoice',
                'reference_id' => $event->purchaseInvoiceId,
                'description' => $description,
                'lines' => $jeLines,
            ]);
        });
    }
}

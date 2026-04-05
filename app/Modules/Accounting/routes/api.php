<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Accounting\Infrastructure\Http\Controllers\AccountController;
use Modules\Accounting\Infrastructure\Http\Controllers\BankAccountController;
use Modules\Accounting\Infrastructure\Http\Controllers\BankTransactionController;
use Modules\Accounting\Infrastructure\Http\Controllers\BudgetController;
use Modules\Accounting\Infrastructure\Http\Controllers\ExpenseCategoryController;
use Modules\Accounting\Infrastructure\Http\Controllers\JournalEntryController;
use Modules\Accounting\Infrastructure\Http\Controllers\PaymentController;
use Modules\Accounting\Infrastructure\Http\Controllers\RefundController;
use Modules\Accounting\Infrastructure\Http\Controllers\ReportController;
use Modules\Accounting\Infrastructure\Http\Controllers\TransactionRuleController;

Route::prefix('api')->middleware('auth:api')->group(function () {
    // Accounts (Chart of Accounts)
    Route::apiResource('accounts', AccountController::class);

    // Journal Entries
    Route::apiResource('journal-entries', JournalEntryController::class);
    Route::post('journal-entries/{id}/post', [JournalEntryController::class, 'post']);
    Route::post('journal-entries/{id}/void', [JournalEntryController::class, 'void']);

    // Bank Accounts
    Route::apiResource('bank-accounts', BankAccountController::class);

    // Bank Transactions
    Route::apiResource('bank-transactions', BankTransactionController::class);
    Route::post('bank-transactions/import', [BankTransactionController::class, 'import']);
    Route::post('bank-transactions/bulk-reclassify', [BankTransactionController::class, 'bulkReclassify']);

    // Expense Categories
    Route::apiResource('expense-categories', ExpenseCategoryController::class);

    // Transaction Rules
    Route::apiResource('transaction-rules', TransactionRuleController::class);

    // Budgets
    Route::apiResource('budgets', BudgetController::class);
    Route::get('budgets/{id}/vs-actual', [BudgetController::class, 'vsActual']);

    // Payments & Refunds
    Route::apiResource('payments', PaymentController::class);
    Route::apiResource('refunds', RefundController::class)->only(['index', 'store', 'show', 'destroy']);

    // Financial Reports
    Route::prefix('reports')->group(function () {
        Route::get('balance-sheet', [ReportController::class, 'balanceSheet']);
        Route::get('profit-loss', [ReportController::class, 'profitLoss']);
        Route::get('cash-flow', [ReportController::class, 'cashFlow']);
    });
});

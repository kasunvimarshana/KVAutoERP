<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Accounting\Application\Contracts\AccountServiceInterface;
use Modules\Accounting\Application\Contracts\BudgetServiceInterface;
use Modules\Accounting\Application\Contracts\BulkReclassifyTransactionsServiceInterface;
use Modules\Accounting\Application\Contracts\CategorizeTransactionServiceInterface;
use Modules\Accounting\Application\Contracts\GenerateFinancialReportServiceInterface;
use Modules\Accounting\Application\Contracts\ImportBankTransactionsServiceInterface;
use Modules\Accounting\Application\Contracts\JournalEntryServiceInterface;
use Modules\Accounting\Application\Contracts\PaymentServiceInterface;
use Modules\Accounting\Application\Contracts\RefundServiceInterface;
use Modules\Accounting\Application\Services\AccountService;
use Modules\Accounting\Application\Services\BudgetService;
use Modules\Accounting\Application\Services\BulkReclassifyTransactionsService;
use Modules\Accounting\Application\Services\CategorizeTransactionService;
use Modules\Accounting\Application\Services\GenerateFinancialReportService;
use Modules\Accounting\Application\Services\ImportBankTransactionsService;
use Modules\Accounting\Application\Services\JournalEntryService;
use Modules\Accounting\Application\Services\PaymentService;
use Modules\Accounting\Application\Services\RefundService;
use Modules\Accounting\Domain\RepositoryInterfaces\AccountRepositoryInterface;
use Modules\Accounting\Domain\RepositoryInterfaces\BankAccountRepositoryInterface;
use Modules\Accounting\Domain\RepositoryInterfaces\BankTransactionRepositoryInterface;
use Modules\Accounting\Domain\RepositoryInterfaces\BudgetRepositoryInterface;
use Modules\Accounting\Domain\RepositoryInterfaces\ExpenseCategoryRepositoryInterface;
use Modules\Accounting\Domain\RepositoryInterfaces\JournalEntryRepositoryInterface;
use Modules\Accounting\Domain\RepositoryInterfaces\PaymentRepositoryInterface;
use Modules\Accounting\Domain\RepositoryInterfaces\RefundRepositoryInterface;
use Modules\Accounting\Domain\RepositoryInterfaces\TransactionRuleRepositoryInterface;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\AccountModel;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\BankAccountModel;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\BankTransactionModel;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\BudgetModel;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\ExpenseCategoryModel;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\JournalEntryModel;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\JournalLineModel;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\PaymentModel;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\RefundModel;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\TransactionRuleModel;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories\EloquentAccountRepository;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories\EloquentBankAccountRepository;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories\EloquentBankTransactionRepository;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories\EloquentBudgetRepository;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories\EloquentExpenseCategoryRepository;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories\EloquentJournalEntryRepository;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories\EloquentPaymentRepository;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories\EloquentRefundRepository;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories\EloquentTransactionRuleRepository;

class AccountingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repositories
        $this->app->bind(AccountRepositoryInterface::class, function ($app) {
            return new EloquentAccountRepository($app->make(AccountModel::class));
        });

        $this->app->bind(JournalEntryRepositoryInterface::class, function ($app) {
            return new EloquentJournalEntryRepository(
                $app->make(JournalEntryModel::class),
                $app->make(JournalLineModel::class),
            );
        });

        $this->app->bind(BankAccountRepositoryInterface::class, function ($app) {
            return new EloquentBankAccountRepository($app->make(BankAccountModel::class));
        });

        $this->app->bind(BankTransactionRepositoryInterface::class, function ($app) {
            return new EloquentBankTransactionRepository($app->make(BankTransactionModel::class));
        });

        $this->app->bind(ExpenseCategoryRepositoryInterface::class, function ($app) {
            return new EloquentExpenseCategoryRepository($app->make(ExpenseCategoryModel::class));
        });

        $this->app->bind(TransactionRuleRepositoryInterface::class, function ($app) {
            return new EloquentTransactionRuleRepository($app->make(TransactionRuleModel::class));
        });

        $this->app->bind(BudgetRepositoryInterface::class, function ($app) {
            return new EloquentBudgetRepository($app->make(BudgetModel::class));
        });

        $this->app->bind(PaymentRepositoryInterface::class, function ($app) {
            return new EloquentPaymentRepository($app->make(PaymentModel::class));
        });

        $this->app->bind(RefundRepositoryInterface::class, function ($app) {
            return new EloquentRefundRepository($app->make(RefundModel::class));
        });

        // Application Services
        $this->app->bind(AccountServiceInterface::class, function ($app) {
            return new AccountService($app->make(AccountRepositoryInterface::class));
        });

        $this->app->bind(JournalEntryServiceInterface::class, function ($app) {
            return new JournalEntryService($app->make(JournalEntryRepositoryInterface::class));
        });

        $this->app->bind(ImportBankTransactionsServiceInterface::class, function ($app) {
            return new ImportBankTransactionsService($app->make(BankTransactionRepositoryInterface::class));
        });

        $this->app->bind(CategorizeTransactionServiceInterface::class, function ($app) {
            return new CategorizeTransactionService($app->make(BankTransactionRepositoryInterface::class));
        });

        $this->app->bind(BulkReclassifyTransactionsServiceInterface::class, function ($app) {
            return new BulkReclassifyTransactionsService($app->make(BankTransactionRepositoryInterface::class));
        });

        $this->app->bind(GenerateFinancialReportServiceInterface::class, function ($app) {
            return new GenerateFinancialReportService(
                $app->make(AccountRepositoryInterface::class),
                $app->make(JournalEntryRepositoryInterface::class),
            );
        });

        $this->app->bind(BudgetServiceInterface::class, function ($app) {
            return new BudgetService(
                $app->make(BudgetRepositoryInterface::class),
                $app->make(AccountRepositoryInterface::class),
            );
        });

        $this->app->bind(PaymentServiceInterface::class, function ($app) {
            return new PaymentService($app->make(PaymentRepositoryInterface::class));
        });

        $this->app->bind(RefundServiceInterface::class, function ($app) {
            return new RefundService(
                $app->make(RefundRepositoryInterface::class),
                $app->make(PaymentRepositoryInterface::class),
            );
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../../../routes/api.php');
    }
}

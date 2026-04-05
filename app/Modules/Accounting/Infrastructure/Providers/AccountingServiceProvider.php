<?php declare(strict_types=1);
namespace Modules\Accounting\Infrastructure\Providers;
use Illuminate\Support\ServiceProvider;
use Modules\Accounting\Application\Services\BulkReclassifyTransactionsService;
use Modules\Accounting\Application\Services\CategorizeTransactionService;
use Modules\Accounting\Application\Services\CreateJournalEntryService;
use Modules\Accounting\Application\Services\GenerateFinancialReportService;
use Modules\Accounting\Domain\RepositoryInterfaces\AccountRepositoryInterface;
use Modules\Accounting\Domain\RepositoryInterfaces\BankTransactionRepositoryInterface;
use Modules\Accounting\Domain\RepositoryInterfaces\JournalEntryRepositoryInterface;
use Modules\Accounting\Domain\RepositoryInterfaces\TransactionRuleRepositoryInterface;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\AccountModel;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\BankTransactionModel;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\JournalEntryModel;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\JournalLineModel;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\TransactionRuleModel;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories\EloquentAccountRepository;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories\EloquentBankTransactionRepository;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories\EloquentJournalEntryRepository;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories\EloquentTransactionRuleRepository;
class AccountingServiceProvider extends ServiceProvider {
    public function register(): void {
        $this->app->bind(AccountRepositoryInterface::class, fn($app) => new EloquentAccountRepository($app->make(AccountModel::class)));
        $this->app->bind(JournalEntryRepositoryInterface::class, fn($app) => new EloquentJournalEntryRepository($app->make(JournalEntryModel::class), $app->make(JournalLineModel::class)));
        $this->app->bind(BankTransactionRepositoryInterface::class, fn($app) => new EloquentBankTransactionRepository($app->make(BankTransactionModel::class)));
        $this->app->bind(TransactionRuleRepositoryInterface::class, fn($app) => new EloquentTransactionRuleRepository($app->make(TransactionRuleModel::class)));
        $this->app->bind(CreateJournalEntryService::class, fn($app) => new CreateJournalEntryService($app->make(JournalEntryRepositoryInterface::class)));
        $this->app->bind(CategorizeTransactionService::class, fn($app) => new CategorizeTransactionService($app->make(BankTransactionRepositoryInterface::class), $app->make(TransactionRuleRepositoryInterface::class)));
        $this->app->bind(BulkReclassifyTransactionsService::class, fn($app) => new BulkReclassifyTransactionsService($app->make(BankTransactionRepositoryInterface::class)));
        $this->app->bind(GenerateFinancialReportService::class, fn($app) => new GenerateFinancialReportService($app->make(AccountRepositoryInterface::class), $app->make(JournalEntryRepositoryInterface::class)));
    }
    public function boot(): void {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }
}

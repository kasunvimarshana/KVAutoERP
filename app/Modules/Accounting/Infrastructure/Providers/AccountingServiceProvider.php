<?php
declare(strict_types=1);
namespace Modules\Accounting\Infrastructure\Providers;
use Illuminate\Support\ServiceProvider;
use Modules\Accounting\Application\Contracts\PostJournalEntryServiceInterface;
use Modules\Accounting\Application\Services\PostJournalEntryService;
use Modules\Accounting\Domain\RepositoryInterfaces\AccountRepositoryInterface;
use Modules\Accounting\Domain\RepositoryInterfaces\JournalEntryRepositoryInterface;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\AccountModel;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\JournalEntryLineModel;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\JournalEntryModel;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories\EloquentAccountRepository;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories\EloquentJournalEntryRepository;
class AccountingServiceProvider extends ServiceProvider {
    public function register(): void {
        $this->app->bind(AccountRepositoryInterface::class, fn($app) => new EloquentAccountRepository($app->make(AccountModel::class)));
        $this->app->bind(JournalEntryRepositoryInterface::class, fn($app) =>
            new EloquentJournalEntryRepository($app->make(JournalEntryModel::class),$app->make(JournalEntryLineModel::class))
        );
        $this->app->bind(PostJournalEntryServiceInterface::class, fn($app) =>
            new PostJournalEntryService($app->make(JournalEntryRepositoryInterface::class),$app->make(AccountRepositoryInterface::class))
        );
    }
    public function boot(): void {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');
    }
}

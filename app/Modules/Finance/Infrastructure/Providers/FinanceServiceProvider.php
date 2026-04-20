<?php

declare(strict_types=1);

namespace Modules\Finance\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Core\Infrastructure\Concerns\LoadsModuleRoutesAndMigrations;
use Modules\Finance\Application\Contracts\CreateAccountServiceInterface;
use Modules\Finance\Application\Contracts\CreateCostCenterServiceInterface;
use Modules\Finance\Application\Contracts\CreateFiscalPeriodServiceInterface;
use Modules\Finance\Application\Contracts\CreateFiscalYearServiceInterface;
use Modules\Finance\Application\Contracts\CreateJournalEntryServiceInterface;
use Modules\Finance\Application\Contracts\CreateNumberingSequenceServiceInterface;
use Modules\Finance\Application\Contracts\CreatePaymentMethodServiceInterface;
use Modules\Finance\Application\Contracts\CreatePaymentServiceInterface;
use Modules\Finance\Application\Contracts\CreatePaymentTermServiceInterface;
use Modules\Finance\Application\Contracts\DeleteAccountServiceInterface;
use Modules\Finance\Application\Contracts\DeleteCostCenterServiceInterface;
use Modules\Finance\Application\Contracts\DeleteFiscalPeriodServiceInterface;
use Modules\Finance\Application\Contracts\DeleteFiscalYearServiceInterface;
use Modules\Finance\Application\Contracts\DeleteJournalEntryServiceInterface;
use Modules\Finance\Application\Contracts\DeleteNumberingSequenceServiceInterface;
use Modules\Finance\Application\Contracts\DeletePaymentMethodServiceInterface;
use Modules\Finance\Application\Contracts\DeletePaymentServiceInterface;
use Modules\Finance\Application\Contracts\DeletePaymentTermServiceInterface;
use Modules\Finance\Application\Contracts\FindAccountServiceInterface;
use Modules\Finance\Application\Contracts\FindCostCenterServiceInterface;
use Modules\Finance\Application\Contracts\FindFiscalPeriodServiceInterface;
use Modules\Finance\Application\Contracts\FindFiscalYearServiceInterface;
use Modules\Finance\Application\Contracts\FindJournalEntryServiceInterface;
use Modules\Finance\Application\Contracts\FindNumberingSequenceServiceInterface;
use Modules\Finance\Application\Contracts\FindPaymentMethodServiceInterface;
use Modules\Finance\Application\Contracts\FindPaymentServiceInterface;
use Modules\Finance\Application\Contracts\FindPaymentTermServiceInterface;
use Modules\Finance\Application\Contracts\PostJournalEntryServiceInterface;
use Modules\Finance\Application\Contracts\UpdateAccountServiceInterface;
use Modules\Finance\Application\Contracts\UpdateCostCenterServiceInterface;
use Modules\Finance\Application\Contracts\UpdateFiscalPeriodServiceInterface;
use Modules\Finance\Application\Contracts\UpdateFiscalYearServiceInterface;
use Modules\Finance\Application\Contracts\UpdateJournalEntryServiceInterface;
use Modules\Finance\Application\Contracts\UpdateNumberingSequenceServiceInterface;
use Modules\Finance\Application\Contracts\UpdatePaymentMethodServiceInterface;
use Modules\Finance\Application\Contracts\UpdatePaymentServiceInterface;
use Modules\Finance\Application\Contracts\UpdatePaymentTermServiceInterface;
use Modules\Finance\Application\Services\CreateAccountService;
use Modules\Finance\Application\Services\CreateCostCenterService;
use Modules\Finance\Application\Services\CreateFiscalPeriodService;
use Modules\Finance\Application\Services\CreateFiscalYearService;
use Modules\Finance\Application\Services\CreateJournalEntryService;
use Modules\Finance\Application\Services\CreateNumberingSequenceService;
use Modules\Finance\Application\Services\CreatePaymentMethodService;
use Modules\Finance\Application\Services\CreatePaymentService;
use Modules\Finance\Application\Services\CreatePaymentTermService;
use Modules\Finance\Application\Services\DeleteAccountService;
use Modules\Finance\Application\Services\DeleteCostCenterService;
use Modules\Finance\Application\Services\DeleteFiscalPeriodService;
use Modules\Finance\Application\Services\DeleteFiscalYearService;
use Modules\Finance\Application\Services\DeleteJournalEntryService;
use Modules\Finance\Application\Services\DeleteNumberingSequenceService;
use Modules\Finance\Application\Services\DeletePaymentMethodService;
use Modules\Finance\Application\Services\DeletePaymentService;
use Modules\Finance\Application\Services\DeletePaymentTermService;
use Modules\Finance\Application\Services\FindAccountService;
use Modules\Finance\Application\Services\FindCostCenterService;
use Modules\Finance\Application\Services\FindFiscalPeriodService;
use Modules\Finance\Application\Services\FindFiscalYearService;
use Modules\Finance\Application\Services\FindJournalEntryService;
use Modules\Finance\Application\Services\FindNumberingSequenceService;
use Modules\Finance\Application\Services\FindPaymentMethodService;
use Modules\Finance\Application\Services\FindPaymentService;
use Modules\Finance\Application\Services\FindPaymentTermService;
use Modules\Finance\Application\Services\PostJournalEntryService;
use Modules\Finance\Application\Services\UpdateAccountService;
use Modules\Finance\Application\Services\UpdateCostCenterService;
use Modules\Finance\Application\Services\UpdateFiscalPeriodService;
use Modules\Finance\Application\Services\UpdateFiscalYearService;
use Modules\Finance\Application\Services\UpdateJournalEntryService;
use Modules\Finance\Application\Services\UpdateNumberingSequenceService;
use Modules\Finance\Application\Services\UpdatePaymentMethodService;
use Modules\Finance\Application\Services\UpdatePaymentService;
use Modules\Finance\Application\Services\UpdatePaymentTermService;
use Modules\Finance\Domain\RepositoryInterfaces\AccountRepositoryInterface;
use Modules\Finance\Domain\RepositoryInterfaces\CostCenterRepositoryInterface;
use Modules\Finance\Domain\RepositoryInterfaces\FiscalPeriodRepositoryInterface;
use Modules\Finance\Domain\RepositoryInterfaces\FiscalYearRepositoryInterface;
use Modules\Finance\Domain\RepositoryInterfaces\JournalEntryRepositoryInterface;
use Modules\Finance\Domain\RepositoryInterfaces\NumberingSequenceRepositoryInterface;
use Modules\Finance\Domain\RepositoryInterfaces\PaymentMethodRepositoryInterface;
use Modules\Finance\Domain\RepositoryInterfaces\PaymentRepositoryInterface;
use Modules\Finance\Domain\RepositoryInterfaces\PaymentTermRepositoryInterface;
use Modules\Finance\Infrastructure\Persistence\Eloquent\Repositories\EloquentAccountRepository;
use Modules\Finance\Infrastructure\Persistence\Eloquent\Repositories\EloquentCostCenterRepository;
use Modules\Finance\Infrastructure\Persistence\Eloquent\Repositories\EloquentFiscalPeriodRepository;
use Modules\Finance\Infrastructure\Persistence\Eloquent\Repositories\EloquentFiscalYearRepository;
use Modules\Finance\Infrastructure\Persistence\Eloquent\Repositories\EloquentJournalEntryRepository;
use Modules\Finance\Infrastructure\Persistence\Eloquent\Repositories\EloquentNumberingSequenceRepository;
use Modules\Finance\Infrastructure\Persistence\Eloquent\Repositories\EloquentPaymentMethodRepository;
use Modules\Finance\Infrastructure\Persistence\Eloquent\Repositories\EloquentPaymentRepository;
use Modules\Finance\Infrastructure\Persistence\Eloquent\Repositories\EloquentPaymentTermRepository;

class FinanceServiceProvider extends ServiceProvider
{
    use LoadsModuleRoutesAndMigrations;

    public function register(): void
    {
        $repositoryBindings = [
            AccountRepositoryInterface::class => EloquentAccountRepository::class,
            CostCenterRepositoryInterface::class => EloquentCostCenterRepository::class,
            FiscalPeriodRepositoryInterface::class => EloquentFiscalPeriodRepository::class,
            FiscalYearRepositoryInterface::class => EloquentFiscalYearRepository::class,
            JournalEntryRepositoryInterface::class => EloquentJournalEntryRepository::class,
            NumberingSequenceRepositoryInterface::class => EloquentNumberingSequenceRepository::class,
            PaymentMethodRepositoryInterface::class => EloquentPaymentMethodRepository::class,
            PaymentRepositoryInterface::class => EloquentPaymentRepository::class,
            PaymentTermRepositoryInterface::class => EloquentPaymentTermRepository::class,
        ];

        foreach ($repositoryBindings as $contract => $implementation) {
            $this->app->bind($contract, $implementation);
        }

        $serviceBindings = [
            CreateAccountServiceInterface::class => CreateAccountService::class,
            FindAccountServiceInterface::class => FindAccountService::class,
            UpdateAccountServiceInterface::class => UpdateAccountService::class,
            DeleteAccountServiceInterface::class => DeleteAccountService::class,
            CreateCostCenterServiceInterface::class => CreateCostCenterService::class,
            FindCostCenterServiceInterface::class => FindCostCenterService::class,
            UpdateCostCenterServiceInterface::class => UpdateCostCenterService::class,
            DeleteCostCenterServiceInterface::class => DeleteCostCenterService::class,
            CreateFiscalYearServiceInterface::class => CreateFiscalYearService::class,
            FindFiscalYearServiceInterface::class => FindFiscalYearService::class,
            UpdateFiscalYearServiceInterface::class => UpdateFiscalYearService::class,
            DeleteFiscalYearServiceInterface::class => DeleteFiscalYearService::class,
            CreateFiscalPeriodServiceInterface::class => CreateFiscalPeriodService::class,
            FindFiscalPeriodServiceInterface::class => FindFiscalPeriodService::class,
            UpdateFiscalPeriodServiceInterface::class => UpdateFiscalPeriodService::class,
            DeleteFiscalPeriodServiceInterface::class => DeleteFiscalPeriodService::class,
            CreateJournalEntryServiceInterface::class => CreateJournalEntryService::class,
            FindJournalEntryServiceInterface::class => FindJournalEntryService::class,
            UpdateJournalEntryServiceInterface::class => UpdateJournalEntryService::class,
            DeleteJournalEntryServiceInterface::class => DeleteJournalEntryService::class,
            PostJournalEntryServiceInterface::class => PostJournalEntryService::class,
            CreateNumberingSequenceServiceInterface::class => CreateNumberingSequenceService::class,
            FindNumberingSequenceServiceInterface::class => FindNumberingSequenceService::class,
            UpdateNumberingSequenceServiceInterface::class => UpdateNumberingSequenceService::class,
            DeleteNumberingSequenceServiceInterface::class => DeleteNumberingSequenceService::class,
            CreatePaymentTermServiceInterface::class => CreatePaymentTermService::class,
            FindPaymentTermServiceInterface::class => FindPaymentTermService::class,
            UpdatePaymentTermServiceInterface::class => UpdatePaymentTermService::class,
            DeletePaymentTermServiceInterface::class => DeletePaymentTermService::class,
            CreatePaymentMethodServiceInterface::class => CreatePaymentMethodService::class,
            FindPaymentMethodServiceInterface::class => FindPaymentMethodService::class,
            UpdatePaymentMethodServiceInterface::class => UpdatePaymentMethodService::class,
            DeletePaymentMethodServiceInterface::class => DeletePaymentMethodService::class,
            CreatePaymentServiceInterface::class => CreatePaymentService::class,
            FindPaymentServiceInterface::class => FindPaymentService::class,
            UpdatePaymentServiceInterface::class => UpdatePaymentService::class,
            DeletePaymentServiceInterface::class => DeletePaymentService::class,
        ];

        foreach ($serviceBindings as $contract => $implementation) {
            $this->app->bind($contract, $implementation);
        }
    }

    public function boot(): void
    {
        $this->bootModule(
            __DIR__.'/../../routes/api.php',
            __DIR__.'/../../database/migrations',
        );
    }
}

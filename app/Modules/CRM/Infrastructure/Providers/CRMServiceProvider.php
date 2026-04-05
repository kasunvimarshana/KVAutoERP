<?php

declare(strict_types=1);

namespace Modules\CRM\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\CRM\Application\Contracts\ActivityServiceInterface;
use Modules\CRM\Application\Contracts\ContactServiceInterface;
use Modules\CRM\Application\Contracts\LeadServiceInterface;
use Modules\CRM\Application\Contracts\OpportunityServiceInterface;
use Modules\CRM\Application\Services\ActivityService;
use Modules\CRM\Application\Services\ContactService;
use Modules\CRM\Application\Services\LeadService;
use Modules\CRM\Application\Services\OpportunityService;
use Modules\CRM\Domain\RepositoryInterfaces\ActivityRepositoryInterface;
use Modules\CRM\Domain\RepositoryInterfaces\ContactRepositoryInterface;
use Modules\CRM\Domain\RepositoryInterfaces\LeadRepositoryInterface;
use Modules\CRM\Domain\RepositoryInterfaces\OpportunityRepositoryInterface;
use Modules\CRM\Infrastructure\Persistence\Eloquent\Models\ActivityModel;
use Modules\CRM\Infrastructure\Persistence\Eloquent\Models\ContactModel;
use Modules\CRM\Infrastructure\Persistence\Eloquent\Models\LeadModel;
use Modules\CRM\Infrastructure\Persistence\Eloquent\Models\OpportunityModel;
use Modules\CRM\Infrastructure\Persistence\Eloquent\Repositories\EloquentActivityRepository;
use Modules\CRM\Infrastructure\Persistence\Eloquent\Repositories\EloquentContactRepository;
use Modules\CRM\Infrastructure\Persistence\Eloquent\Repositories\EloquentLeadRepository;
use Modules\CRM\Infrastructure\Persistence\Eloquent\Repositories\EloquentOpportunityRepository;

class CRMServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ContactRepositoryInterface::class, fn ($app) => new EloquentContactRepository($app->make(ContactModel::class)));
        $this->app->bind(LeadRepositoryInterface::class, fn ($app) => new EloquentLeadRepository($app->make(LeadModel::class)));
        $this->app->bind(OpportunityRepositoryInterface::class, fn ($app) => new EloquentOpportunityRepository($app->make(OpportunityModel::class)));
        $this->app->bind(ActivityRepositoryInterface::class, fn ($app) => new EloquentActivityRepository($app->make(ActivityModel::class)));

        $this->app->bind(ContactServiceInterface::class, fn ($app) => new ContactService($app->make(ContactRepositoryInterface::class)));
        $this->app->bind(LeadServiceInterface::class, fn ($app) => new LeadService(
            $app->make(LeadRepositoryInterface::class),
            $app->make(OpportunityRepositoryInterface::class),
        ));
        $this->app->bind(OpportunityServiceInterface::class, fn ($app) => new OpportunityService($app->make(OpportunityRepositoryInterface::class)));
        $this->app->bind(ActivityServiceInterface::class, fn ($app) => new ActivityService($app->make(ActivityRepositoryInterface::class)));
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }
}

<?php declare(strict_types=1);
namespace Modules\CRM\Infrastructure\Providers;
use Illuminate\Support\ServiceProvider;
use Modules\CRM\Domain\RepositoryInterfaces\ContactRepositoryInterface;
use Modules\CRM\Domain\RepositoryInterfaces\LeadRepositoryInterface;
use Modules\CRM\Infrastructure\Persistence\Eloquent\Models\ContactModel;
use Modules\CRM\Infrastructure\Persistence\Eloquent\Models\LeadModel;
use Modules\CRM\Infrastructure\Persistence\Eloquent\Repositories\EloquentContactRepository;
use Modules\CRM\Infrastructure\Persistence\Eloquent\Repositories\EloquentLeadRepository;
class CRMServiceProvider extends ServiceProvider {
    public function register(): void {
        $this->app->bind(ContactRepositoryInterface::class, fn($app)=>new EloquentContactRepository($app->make(ContactModel::class)));
        $this->app->bind(LeadRepositoryInterface::class, fn($app)=>new EloquentLeadRepository($app->make(LeadModel::class)));
    }
    public function boot(): void { $this->loadMigrationsFrom(__DIR__.'/../../database/migrations'); }
}

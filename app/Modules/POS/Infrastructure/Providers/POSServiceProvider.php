<?php declare(strict_types=1);
namespace Modules\POS\Infrastructure\Providers;
use Illuminate\Support\ServiceProvider;
use Modules\POS\Domain\RepositoryInterfaces\POSSessionRepositoryInterface;
use Modules\POS\Domain\RepositoryInterfaces\TerminalRepositoryInterface;
use Modules\POS\Infrastructure\Persistence\Eloquent\Models\POSSessionModel;
use Modules\POS\Infrastructure\Persistence\Eloquent\Models\TerminalModel;
use Modules\POS\Infrastructure\Persistence\Eloquent\Repositories\EloquentPOSSessionRepository;
use Modules\POS\Infrastructure\Persistence\Eloquent\Repositories\EloquentTerminalRepository;
class POSServiceProvider extends ServiceProvider {
    public function register(): void {
        $this->app->bind(TerminalRepositoryInterface::class, fn($app)=>new EloquentTerminalRepository($app->make(TerminalModel::class)));
        $this->app->bind(POSSessionRepositoryInterface::class, fn($app)=>new EloquentPOSSessionRepository($app->make(POSSessionModel::class)));
    }
    public function boot(): void { $this->loadMigrationsFrom(__DIR__.'/../../database/migrations'); }
}

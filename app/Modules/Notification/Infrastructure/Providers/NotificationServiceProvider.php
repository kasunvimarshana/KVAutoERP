<?php declare(strict_types=1);
namespace Modules\Notification\Infrastructure\Providers;
use Illuminate\Support\ServiceProvider;
use Modules\Notification\Application\Services\SendNotificationService;
use Modules\Notification\Domain\RepositoryInterfaces\NotificationRepositoryInterface;
use Modules\Notification\Infrastructure\Channels\DatabaseChannel;
use Modules\Notification\Infrastructure\Channels\NotificationChannelDispatcher;
use Modules\Notification\Infrastructure\Persistence\Eloquent\Models\NotificationModel;
use Modules\Notification\Infrastructure\Persistence\Eloquent\Repositories\EloquentNotificationRepository;
class NotificationServiceProvider extends ServiceProvider {
    public function register(): void {
        $this->app->bind(NotificationRepositoryInterface::class, fn($app)=>new EloquentNotificationRepository($app->make(NotificationModel::class)));
        $this->app->singleton(NotificationChannelDispatcher::class, function($app) {
            $dispatcher = new NotificationChannelDispatcher();
            $dispatcher->register(new DatabaseChannel());
            return $dispatcher;
        });
        $this->app->bind(SendNotificationService::class, fn($app)=>new SendNotificationService($app->make(NotificationRepositoryInterface::class),$app->make(NotificationChannelDispatcher::class)));
    }
    public function boot(): void { $this->loadMigrationsFrom(__DIR__.'/../../database/migrations'); }
}

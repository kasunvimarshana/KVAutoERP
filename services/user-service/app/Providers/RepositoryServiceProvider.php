<?php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\UserProfileRepositoryInterface;
use App\Repositories\UserProfileRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserProfileRepositoryInterface::class, UserProfileRepository::class);
    }
}

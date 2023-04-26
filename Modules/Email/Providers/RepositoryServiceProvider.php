<?php
namespace Modules\Email\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Email\Repositories\Email\EmailReposiotries;
use Modules\Email\Repositories\Email\EmailRepositoryInterface;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(EmailRepositoryInterface::class,EmailReposiotries::class);
    }
}
<?php
namespace Modules\JobNotify\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\JobNotify\Repositories\NotifyStaff\NotifyStaffRepo;
use Modules\JobNotify\Repositories\NotifyStaff\NotifyStaffRepoInterface;


class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // TODO: Khai báo cái repository ở đây
        $this->app->singleton(NotifyStaffRepoInterface::class, NotifyStaffRepo::class);
    }
}
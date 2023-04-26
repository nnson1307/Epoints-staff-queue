<?php
namespace Modules\Commission\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Commission\Repositories\StaffCommission\StaffCommissionRepo;
use Modules\Commission\Repositories\StaffCommission\StaffCommissionRepoInterface;


class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(StaffCommissionRepoInterface::class, StaffCommissionRepo::class);
    }
}
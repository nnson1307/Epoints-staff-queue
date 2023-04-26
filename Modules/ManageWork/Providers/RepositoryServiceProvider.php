<?php
namespace Modules\ManageWork\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\ManageWork\Repositories\ManageProject\ManageProjectRepo;
use Modules\ManageWork\Repositories\ManageProject\ManageProjectRepoInterface;
use Modules\ManageWork\Repositories\ManageWork\ManageWorkRepositories;
use Modules\ManageWork\Repositories\ManageWork\ManageWorkRepositoryInterface;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(ManageWorkRepositoryInterface::class,ManageWorkRepositories::class);
        $this->app->singleton(ManageProjectRepoInterface::class,ManageProjectRepo::class);
    }
}
<?php
namespace Modules\Admin\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Admin\Repositories\Brand\BrandInterface;
use Modules\Admin\Repositories\Brand\BrandRepo;

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
        $this->app->singleton(BrandInterface::class, BrandRepo::class);
    }
}

<?php

namespace App\Providers;

use App\Repositories\MyStoreGroup\MyStoreGroupRepository;
use App\Repositories\MyStoreGroup\MyStoreGroupRepositoryInterface;
use App\Repositories\PushNotification\PushNotificationInterface;
use App\Repositories\PushNotification\PushNotificationRepo;
use App\Repositories\Register\RegisterInterface;
use App\Repositories\Register\RegisterRepo;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(RegisterInterface::class, RegisterRepo::class);
        $this->app->singleton(PushNotificationInterface::class, PushNotificationRepo::class);
        $this->app->singleton(MyStoreGroupRepositoryInterface::class, MyStoreGroupRepository::class);
    }
}

<?php
namespace Modules\Notification\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Notification\Repositories\PushNotification\PushNotificationInterface;
use Modules\Notification\Repositories\PushNotification\PushNotificationRepo;
use Modules\Notification\Repositories\Register\RegisterInterface;
use Modules\Notification\Repositories\Register\RegisterRepo;

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
        $this->app->singleton(RegisterInterface::class, RegisterRepo::class);
        $this->app->singleton(PushNotificationInterface::class, PushNotificationRepo::class);
    }
}
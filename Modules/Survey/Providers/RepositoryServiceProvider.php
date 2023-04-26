<?php

namespace Modules\Survey\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Survey\Repositories\Branch\ApplyRepository;
use Modules\Survey\Repositories\JobNotify\JobNotifyRepo;
use Modules\Survey\Repositories\Survey\SurveyRepository;
use Modules\Survey\Repositories\Branch\ApplyRepositoryInterface;
use Modules\Survey\Repositories\JobNotify\JobNotifyRepoInterface;
use Modules\Survey\Repositories\Survey\SurveyRepositoryInterface;
use Modules\Survey\Repositories\Customer\CustomerGroupFilterRepository;
use Modules\Survey\Repositories\Customer\CustomerGroupFilterRepositoryInterface;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //Mẫu
        $this->app->singleton(
            SurveyRepositoryInterface::class,
            SurveyRepository::class
        );
        // branch // 
        $this->app->singleton(
            ApplyRepositoryInterface::class,
            ApplyRepository::class
        );

        //  // 
        $this->app->singleton(
            CustomerGroupFilterRepositoryInterface::class,
            CustomerGroupFilterRepository::class
        );

        // noti // 
        $this->app->singleton(
            JobNotifyRepoInterface::class,
            JobNotifyRepo::class
        );

    }

}

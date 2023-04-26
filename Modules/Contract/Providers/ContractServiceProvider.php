<?php

namespace Modules\Contract\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;
use Modules\Contract\Console\RemindComingEndNotify;
use Modules\Contract\Console\RemindContractExpiredNotify;
use Modules\Contract\Console\RemindDueReceiptSpendNotify;
use Modules\Contract\Console\RemindExpectedRevenueNotify;
use Modules\Contract\Console\RemindWarrantyComingEndNotify;
use Modules\Contract\Console\RemindWarrantyExpiredNotify;
use Modules\Contract\Console\SendContractStaffNotification;

class ContractServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerFactories();
        $this->registerCommands();
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
        $this->app->register(RepositoryServiceProvider::class);
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('contract.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'contract'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/contract');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ],'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/contract';
        }, \Config::get('view.paths')), [$sourcePath]), 'contract');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/contract');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'contract');
        } else {
            $this->loadTranslationsFrom(__DIR__ .'/../Resources/lang', 'contract');
        }
    }

    /**
     * Register an additional directory of factories.
     * 
     * @return void
     */
    public function registerFactories()
    {
        if (! app()->environment('production')) {
            app(Factory::class)->load(__DIR__ . '/../Database/factories');
        }
    }

    /**
     * Register commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        $this->commands([
            SendContractStaffNotification::class,
            RemindExpectedRevenueNotify::class,
            RemindDueReceiptSpendNotify::class,
            RemindComingEndNotify::class,
            RemindContractExpiredNotify::class,
            RemindWarrantyExpiredNotify::class,
            RemindWarrantyComingEndNotify::class
        ]);
    }
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}

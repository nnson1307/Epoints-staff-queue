<?php


namespace Modules\Shift\Providers;


use Illuminate\Support\ServiceProvider;
use Modules\Shift\Repositories\ConfigNotiRepository;
use Modules\Shift\Repositories\ConfigNotiRepositoryInterface;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register() {
        $this->app->singleton(ConfigNotiRepositoryInterface::class,ConfigNotiRepository::class);
    }
}
<?php
namespace Modules\Contract\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Contract\Repositories\ComingEnd\ComingEndRepo;
use Modules\Contract\Repositories\ComingEnd\ComingEndRepoInterface;
use Modules\Contract\Repositories\ContractExpired\ContractExpiredRepo;
use Modules\Contract\Repositories\ContractExpired\ContractExpiredRepoInterface;
use Modules\Contract\Repositories\DueReceiptSpend\DueReceiptSpendRepo;
use Modules\Contract\Repositories\DueReceiptSpend\DueReceiptSpendRepoInterface;
use Modules\Contract\Repositories\ExpectedRevenue\ExpectedRevenueRepo;
use Modules\Contract\Repositories\ExpectedRevenue\ExpectedRevenueRepoInterface;
use Modules\Contract\Repositories\WarrantyComingEnd\WarrantyComingEndRepo;
use Modules\Contract\Repositories\WarrantyComingEnd\WarrantyComingEndRepoInterface;
use Modules\Contract\Repositories\WarrantyExpired\WarrantyExpiredRepo;
use Modules\Contract\Repositories\WarrantyExpired\WarrantyExpiredRepoInterface;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(ExpectedRevenueRepoInterface::class,ExpectedRevenueRepo::class);
        $this->app->singleton(DueReceiptSpendRepoInterface::class, DueReceiptSpendRepo::class);
        $this->app->singleton(ComingEndRepoInterface::class, ComingEndRepo::class);
        $this->app->singleton(ContractExpiredRepoInterface::class, ContractExpiredRepo::class);
        $this->app->singleton(WarrantyExpiredRepoInterface::class, WarrantyExpiredRepo::class);
        $this->app->singleton(WarrantyComingEndRepoInterface::class, WarrantyComingEndRepo::class);
    }
}
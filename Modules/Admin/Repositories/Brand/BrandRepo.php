<?php
namespace Modules\Admin\Repositories\Brand;

use Carbon\Carbon;
use Modules\Admin\Models\Backoffice\BrandTable;

/**
 * Class RegisterRepo
 * @package Modules\Notification\Repositories\Register
 * @author DaiDP
 * @since Aug, 2020
 */
class BrandRepo implements BrandInterface
{
    /**
     * @var SnsClient
     */
    protected $client;
    protected $_mDeviceToken;
    protected $mBrand;

    /**
     * RegisterRepo constructor.
     */
    public function __construct(BrandTable $brandTable)
    {
        $this->mBrand = $brandTable;
    }

    public function getAllBrand($filter){
        return $this->mBrand->getAll($filter);
    }

    public function getAllBrandBySocial($filter){
        return $this->mBrand->getAllBySocial($filter);
    }

    /**
     * Lấy ds brand bằng client key
     *
     * @param $filter
     * @return mixed
     */
    public function getBrandByClient($filter)
    {
        return $this->mBrand->getBrandByClient($filter);
    }
}

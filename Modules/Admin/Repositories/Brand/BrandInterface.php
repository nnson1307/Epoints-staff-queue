<?php
namespace Modules\Admin\Repositories\Brand;

/**
 * Interface RegisterInterface
 * @package App\Repositories\Register
 * @author DaiDP
 * @since Aug, 2020
 */
interface BrandInterface
{
    public function getAllBrand($filter);

    public function getAllBrandBySocial($filter);

    /**
     * Lấy ds brand bằng client key
     *
     * @param $filter
     * @return mixed
     */
    public function getBrandByClient($filter);
}

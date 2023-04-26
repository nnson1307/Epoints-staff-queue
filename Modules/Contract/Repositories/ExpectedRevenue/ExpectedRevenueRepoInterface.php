<?php
/**
 * Created by PhpStorm.
 * User: nguyenngocson
 * Date: 24/11/2021
 * Time: 16:00
 */

namespace Modules\Contract\Repositories\ExpectedRevenue;


interface ExpectedRevenueRepoInterface
{
    /**
     * Job lưu log nhắc nhở thu - chi
     *
     * @return mixed
     */
    public function jobSaveLogExpectedRevenue();
}
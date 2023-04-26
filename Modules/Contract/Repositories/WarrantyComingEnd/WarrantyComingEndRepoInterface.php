<?php
/**
 * Created by PhpStorm.
 * User: nguyenngocson
 * Date: 28/11/2021
 * Time: 14:23
 */

namespace Modules\Contract\Repositories\WarrantyComingEnd;


interface WarrantyComingEndRepoInterface
{
    /**
     * Job lưu log HĐ sắp hết hạn bảo hành
     *
     * @return mixed
     */
    public function jobSaveLogWarrantyComingEnd();
}
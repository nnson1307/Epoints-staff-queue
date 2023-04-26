<?php
/**
 * Created by PhpStorm.
 * User: nguyenngocson
 * Date: 26/11/2021
 * Time: 17:20
 */

namespace Modules\Contract\Repositories\WarrantyExpired;


interface WarrantyExpiredRepoInterface
{
    /**
     * Nhắc nhở HĐ hết hạn bảo hành
     *
     * @return mixed
     */
    public function jobSaveLogWarrantyExpired();
}
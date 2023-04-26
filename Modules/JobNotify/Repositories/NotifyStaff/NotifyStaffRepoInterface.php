<?php
/**
 * Created by PhpStorm.
 * User: nguyenngocson
 * Date: 09/06/2022
 * Time: 10:39
 */

namespace Modules\JobNotify\Repositories\NotifyStaff;


interface NotifyStaffRepoInterface
{
    /**
     * Gửi thông báo
     *
     * @param $input
     * @return mixed
     */
    public function saveLogNotifyStaff($input);
}
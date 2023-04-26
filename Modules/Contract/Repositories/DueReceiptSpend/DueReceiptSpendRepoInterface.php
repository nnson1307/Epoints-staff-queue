<?php
/**
 * Created by PhpStorm.
 * User: nguyenngocson
 * Date: 26/11/2021
 * Time: 10:06
 */

namespace Modules\Contract\Repositories\DueReceiptSpend;


interface DueReceiptSpendRepoInterface
{
    /**
     * Job lưu log nhắc nhở đến hạn thu - chi
     *
     * @return mixed
     */
    public function jobSaveLogDue();
}
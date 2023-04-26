<?php
/**
 * Created by PhpStorm.
 * User: nguyenngocson
 * Date: 26/11/2021
 * Time: 15:52
 */

namespace Modules\Contract\Repositories\ContractExpired;


interface ContractExpiredRepoInterface
{
    /**
     * Nhắc nhở HĐ hết hạn
     *
     * @return mixed
     */
    public function jobSaveLogContractExpired();
}
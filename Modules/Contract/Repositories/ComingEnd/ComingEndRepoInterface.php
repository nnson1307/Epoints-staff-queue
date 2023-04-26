<?php
/**
 * Created by PhpStorm.
 * User: nguyenngocson
 * Date: 26/11/2021
 * Time: 14:34
 */

namespace Modules\Contract\Repositories\ComingEnd;


interface ComingEndRepoInterface
{
    /**
     * Job nhắc nhở HĐ sắp hết hạn
     *
     * @return mixed
     */
    public function jobSaveLogComingEnd();
}
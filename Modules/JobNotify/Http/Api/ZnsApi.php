<?php
/**
 * Created by PhpStorm.
 * User: nguyenngocson
 * Date: 25/01/2022
 * Time: 14:56
 */

namespace Modules\JobNotify\Http\Api;

use MyCore\Api\ApiAbstract;

class ZnsApi extends ApiAbstract
{
    /**
     * Get template ZNS về
     *
     * @param array $data
     * @return mixed
     * @throws \MyCore\Api\ApiException
     */
    public function sendZns(array $data = [])
    {
        return $this->baseClientShareService('/noti/zalo/zns/send', $data, false);
    }

    /**
     * Get template Follower về
     *
     * @param array $data
     * @return mixed
     * @throws \MyCore\Api\ApiException
     */
    public function sendFollower($data)
    {
        return $this->baseClientShareService('/noti/zalo/zns/send-follower', $data, false);
    }

    /**
     * Lưu log trigger event Zns
     *
     * @param array $data
     * @return mixed
     * @throws \MyCore\Api\ApiException
     */
    public function saveLogTriggerEvent(array $data = [])
    {
        return $this->baseClientShareService('/zns/trigger-event', $data, false);
    }
}
<?php
namespace MyCore\Helper;

use App\Jobs\PnsRegisterToken;
use App\Entities\RegisterPNSTokenMessage;

/**
 * Trait RegisterDeviceToken
 * @package MyCore\Helper
 * @author DaiDP
 * @since Aug, 2019
 */
trait RegisterDeviceToken
{
    /**
     * Gọi job gửi notification
     *
     * @param $platform
     * @param $token
     * @param $imei
     */
    protected function registerDeviceToken($platform, $token, $imei)
    {
        $message = new RegisterPNSTokenMessage([
            'user_id'  => auth()->id(),
            'platform' => $platform,
            'device_token' => $token,
            'imei'     => $imei
        ]);

        $job = new PnsRegisterToken($message);
        dispatch($job);
    }
}
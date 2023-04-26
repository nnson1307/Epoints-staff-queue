<?php
namespace Modules\Notification\Repositories\Register;

/**
 * Interface RegisterInterface
 * @package App\Repositories\Register
 * @author DaiDP
 * @since Aug, 2020
 */
interface RegisterInterface
{
    /**
     * Đăng ký device token
     *
     * @param RegisterTokenMessage $platform
     * @param $deviceToken
     * @return string|null
     */
    public function register($message);

    /**
     * Subscriber topic
     *
     * @param $endpointArn
     * @param $topic
     * @return string|null
     */
    public function subscriberTopic($endpointArn, $topic);
}

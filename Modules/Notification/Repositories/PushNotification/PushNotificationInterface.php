<?php
namespace Modules\Notification\Repositories\PushNotification;

use Modules\Notification\Entities\BroadcastMessage;
use Modules\Notification\Entities\SendTopicMessage;
use Modules\Notification\Entities\UnicastMessage;

/**
 * Interface PushNotificationInterface
 * @package Modules\Notification\Repositories\PushNotification
 * @author DaiDP
 * @since Aug, 2020
 */
interface PushNotificationInterface
{
    /**
     * Push notification all user my store
     *
     * @param BroadcastMessage $data
     * @return mixed
     */
    public function broadcast(BroadcastMessage $data);

    /**
     * Push notification unicast cho từng user
     *
     * @param UnicastMessage $data
     * @return mixed
     */
    public function unicast(UnicastMessage $data);

    /**
     * Push notification to group
     *
     * @param SendTopicMessage $data
     * @return mixed
     */
    public function sendTopic(SendTopicMessage $data);
}
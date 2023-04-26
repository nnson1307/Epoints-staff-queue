<?php
namespace Modules\Notification\Jobs;

use Modules\Notification\Entities\BroadcastMessage;
use Modules\Notification\Repositories\PushNotification\PushNotificationInterface;
use MyCore\Jobs\BaseJob;

/**
 * Class BroadcastJob
 * @package Modules\Notification\Jobs
 * @author DaiDP
 * @since Aug, 2020
 */
class BroadcastJob extends BaseJob
{
    public $queue = 'noti';
    public $tries = 1;

    /**
     * @var BroadcastMessage
     */
    protected $message;


    /**
     * BroadcastJob constructor.
     * @param BroadcastMessage $message
     */
    public function __construct(BroadcastMessage $message)
    {
        $this->message = $message;
    }

    /**
     * Execute the job.
     * @param PushNotificationInterface $noti
     */
    public function handle(PushNotificationInterface $noti)
    {
        $noti->broadcast($this->message);

        /*
        // Gửi ngay nếu không đặt lịch
        if (empty($this->message->schedule)) {
            $noti->broadcast($this->message);
            return;
        }

        $curTime = Carbon::now();
        try {
            $scheduleTime = Carbon::createFromTimeString($this->message->schedule);
        } catch (\Exception $ex) {
            echo 'Định dạng ngày không đúng.';
            Log::error('[Broadcast] Định dạng ngày không đúng.', $this->message->toArray());
            return;
        }

        // Thời gian đặt lịch hơn thời gian hiện tại 1 phút thì đưa vào queue đặt lịch
        if ($curTime->diffInMinutes($scheduleTime, false) > 1) {
            $mQueue = new NotificationQueueTable();
            $mQueue->scheduleBroadcast($this->message);
            return;
        }

        // Thời gian đặt lịch bé hơn thời gian hiện tại thì gửi luôn
        $noti->broadcast($this->message); */
    }
}

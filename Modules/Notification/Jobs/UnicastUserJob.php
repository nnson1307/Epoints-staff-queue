<?php
namespace Modules\Notification\Jobs;

use Modules\Notification\Entities\UnicastMessage;
use Modules\Notification\Repositories\PushNotification\PushNotificationInterface;
use MyCore\Jobs\BaseJob;

/**
 * Class UnicastUserJob
 * @package Modules\Notification\Jobs
 * @author DaiDP
 * @since Aug, 2020
 */
class UnicastUserJob extends BaseJob
{
    public $queue = 'noti';
    public $tries = 1;

    /**
     * @var UnicastMessage
     */
    protected $message;


    /**
     * UnicastUserJob constructor.
     * @param UnicastMessage $message
     */
    public function __construct(UnicastMessage $message)
    {
        $this->message = $message;
    }

    /**
     * Execute the job.
     * @param PushNotificationInterface $noti
     */
    public function handle(PushNotificationInterface $noti)
    {
        $noti->unicast($this->message);

        /*
        // Gửi ngay nếu không đặt lịch
        if (empty($this->message->schedule)) {
            $noti->unicast($this->message);
            return;
        }

        $curTime = Carbon::now();
        try {
            $scheduleTime = Carbon::createFromTimeString($this->message->schedule);
        } catch (\Exception $ex) {
            echo 'Định dạng ngày không đúng.';
            Log::error('[Unicast] Định dạng ngày không đúng.', $this->message->toArray());
            return;
        }

        // Thời gian đặt lịch hơn thời gian hiện tại 1 phút thì đưa vào queue đặt lịch
        if ($curTime->diffInMinutes($scheduleTime, false) > 1) {
            $mQueue = new NotificationQueueTable();
            $mQueue->scheduleUnicast($this->message);
            return;
        }

        // Thời gian đặt lịch bé hơn thời gian hiện tại thì gửi luôn
        $noti->unicast($this->message); */
    }
}

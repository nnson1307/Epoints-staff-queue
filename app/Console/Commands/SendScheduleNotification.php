<?php
namespace App\Console\Commands;

use App\Entities\BroadcastBrandMessage;
use App\Entities\BroadcastMessage;
use App\Entities\SendGroupMessage;
use App\Entities\UnicastUserMessage;
use App\Jobs\BroadcastBrandJob;
use App\Jobs\BroadcastJob;
use App\Jobs\SendGroupJob;
use App\Jobs\UnicastUserJob;
use App\Models\NotificationQueueTable;
use Illuminate\Console\Command;

/**
 * Class SendScheduleNotification
 * @package App\Console\Commands
 * @author DaiDP
 * @since Sep, 2019
 */
class SendScheduleNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pns:send_schedule';

    /**
     * The console command description
     *
     * @var string
     */
    protected $description = 'Gửi các notification đặt lịch';


    /**
     * Execute the console command
     *
     * @param NotificationQueueTable $mNotiQueue
     */
    public function handle(NotificationQueueTable $mNotiQueue)
    {
        $arrList = $mNotiQueue->getTimedList();
        $this->line('Start with '. count($arrList) .' items in queue');

        foreach ($arrList as $item) {
            $item = (array) $item;

            switch ($item['send_type']) {
                case 'all':
                    if (!empty($item['tenant_id'])) {
                        $data = $this->buildMessageData($item);
                        $msg  = new BroadcastBrandMessage($data);
                        $job  = new BroadcastBrandJob($msg);
                        dispatch($job);
                    }
                    else {
                        $data = $this->buildMessageData($item);
                        $msg  = new BroadcastMessage($data);
                        $job  = new BroadcastJob($msg);
                        dispatch($job);
                    }
                    break;

                case  'group':
                    $data = $this->buildGroupData($item);
                    $msg  = new SendGroupMessage($data);
                    $job  = new SendGroupJob($msg);
                    dispatch($job);
                    break;

                case 'unicast':
                    $data = $this->buildUnicastData($item);
                    $msg  = new UnicastUserMessage($data);
                    $job  = new UnicastUserJob($msg);
                    dispatch($job);
                    break;
            }
        }

        $this->line('Done.');
    }

    /**
     * Build message push notification
     *
     * @param $item
     * @return array
     */
    protected function buildMessageData($item)
    {
        return [
            'title'     => $item['notification_title'],
            'message'   => $item['notification_message'],
            'avatar'    => $item['notification_avatar'],
            'tenant_id' => $item['tenant_id'],
            'detail_id' => $item['notification_detail_id']
        ];
    }

    /**
     * Build message push noti unicast
     *
     * @param $item
     * @return array
     */
    protected function buildUnicastData($item)
    {
        $data = $this->buildMessageData($item);
        $data['user_id'] = $item['send_type_object'];

        return $data;
    }

    /**
     * Build message push noti group
     *
     * @param $item
     * @return array
     */
    protected function buildGroupData($item)
    {
        $data = $this->buildMessageData($item);
        $data['group_id'] = $item['send_type_object'];

        return $data;
    }
}
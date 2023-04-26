<?php

namespace Modules\Notification\Repositories\PushNotification;

use Aws\Sns\SnsClient;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Notification\Entities\BroadcastMessage;
use Modules\Notification\Entities\PayloadMessage;
use Modules\Notification\Entities\SendTopicMessage;
use Modules\Notification\Entities\UnicastMessage;
use Modules\Notification\Jobs\UnicastUserJob;
use Modules\Notification\Models\StaffDeviceTable;
use Modules\Notification\Models\StaffNotificationDetailTable;
use Modules\Notification\Models\StaffNotificationTable;
use Modules\Notification\Models\StaffTable;
use MyCore\Http\Response\ResponseFormatTrait;

/**
 * Class PushNotificationRepo
 * @package Modules\Notification\Repositories\PushNotification
 * @author DaiDP
 * @since Aug, 2020
 */
class PushNotificationRepo implements PushNotificationInterface
{
    use ResponseFormatTrait, ValidatesRequests;
    const SOUND_IOS = "https://epoint-bucket.s3.ap-southeast-1.amazonaws.com/0f73a056d6c12b508a05eea29735e8a52022/04/25/oUDjR3165087028225042022.wav";

    /**
     * @var SnsClient
     */
    protected $client;

    /**
     * RegisterRepo constructor.
     */
    public function __construct()
    {
        $this->client = app('aws')->createClient('sns');
    }


    /**
     * @inheritDoc
     */
    public function broadcast(BroadcastMessage $data)
    {
        // TODO: Lấy danh sách user active
        $mUser = new StaffTable();
        $userList = $mUser->getUserActive();

        // tiến hành gửi
        $this->pushList($userList, $data);
    }

    /**
     * @inheritDoc
     */
    public function unicast(UnicastMessage $data)
    {
        $switchDb = switch_brand_db($data->tenant_id);

        if (!$switchDb) return;

        //Check user_id là mãng hay object
        if (is_array($data->staff_id)) {
            $arrUserId = $data->staff_id;

            unset($data->staff_id);
            //Mãng user_id
            if (count($arrUserId) > 0) {
                foreach ($arrUserId as $v) {
                    $data->staff_id = $v;
                    //Gửi thông báo đến khách hàng
                    $this->pushNotify($data);
                }
            }
        } else {
            //object user_id, gửi thông báo đến khách hàng
            $this->pushNotify($data);
        }
    }

    /**
     * Gửi thông báo đến khách hàng
     *
     * @param $data
     */
    public function pushNotify($data)
    {
        try {
            // TODO: Lấy thiết bị của user
            $mDevice = new StaffDeviceTable();
            $deviceList = $mDevice->getUserDevice($data->staff_id);

            if (isset($data->detail_id) && $data->detail_id != null) {
                // TODO: Ghi log notification
                // tạm thời đã ghi log bên api
                list($idNotification, $bages) = $this->addLogNotification($data);
            } else {
                $idNotification = null;
                $bages = null;
            }

//        // TODO: không có thiết bị thì ngừng
            if (!$deviceList->count()) {
                return;
            }

            $arrParams = isset($data->data) ? $data->data : [];

            if (isset($data->detail_id) && $data->detail_id != null) {
                $mNotificationDetail = new StaffNotificationDetailTable();
                $arrDetail = $mNotificationDetail->getDetailById($data->detail_id);
                if ($arrDetail != null){
                    $arrParams = json_decode($arrDetail['action_params'], true);
                    $arrParams['notification_type'] = $arrDetail['action'];
                    $arrParams['content'] = $arrDetail['content'];
                    $arrParams['action_name'] = $arrDetail['action_name'];
                    $arrParams['background'] = $arrDetail['background'];
                    $arrParams['notification_id'] = $idNotification;
                    $arrParams['staff_notification_id'] = $idNotification;
                    $arrParams['staff_notification_detail_id'] = $arrDetail['staff_notification_detail_id'];
                }
            }

            // TODO: Build payload
            $payload = $this->buildMessage($data->title, $data->message, $bages, $idNotification, $arrParams);

            // TODO: Gửi đến list thiết bị của user
            foreach ($deviceList as $device) {
                try {
                    $this->client->publish([
                        'Message' => $payload,
                        'MessageStructure' => 'json',
                        'TargetArn' => $device->endpoint_arn
                    ]);
                } catch (\Exception $ex) {
                    // Xử lý lỗi ở đây
                }
            }
        }catch (\Exception $e){
            var_dump($e->getMessage().$e->getLine());
        }
    }

    /**
     * @inheritDoc
     */
    public function sendTopic(SendTopicMessage $data)
    {
        // TODO: Implement sendTopic() method.
        $payload = $this->buildMessage($data->title, $data->message);

        try {
            $this->client->publish([
                'Message' => $payload,
                'MessageStructure' => 'json',
                'TopicArn' => $data->topic
            ]);
        } catch (\Exception $ex) {
            // Xử lý lỗi ở đây
        }
    }

    /**
     * Build message to push
     *
     * @param $title
     * @param $message
     * @param null $bages
     * @param null $idNotification
     * @param array $data
     * @return false|string
     */
    protected function buildMessage($title, $message, $bages = null, $idNotification = null, $data = [])
    {
        // TODO: Gắng sự kiện cho fluter hoạt động
        $data['click_action'] = 'FLUTTER_NOTIFICATION_CLICK';
        $data['notification_id'] = $idNotification;

        // TODO: Khởi tạo payload message
        $payloadMessage = new PayloadMessage([
            'title' => $title,
            'message' => $message,
            'badges' => $bages,
            'data' => $data
        ]);

        return json_encode([
            'default' => $message,
            'GCM' => $this->buildFcmPayload($payloadMessage),
            'APNS' => $this->buildApnsPayload($payloadMessage)
        ]);
    }

    /**
     * Build APNS payload
     *
     * @param PayloadMessage $message
     * @return false|string
     */
    protected function buildApnsPayload(PayloadMessage $message)
    {
        return json_encode([
            'aps' => [
                'alert' => [
                    'title' => $message->title,
                    'body' => $message->message,
                    'badge' => $message->badges,
                ],
                'badge' => $message->badges,
                'sound' => "tingeling.wav"
            ],
            'data' => $message->data
        ]);
    }


    /**
     * Build FCM payload
     *
     * @param PayloadMessage $message
     * @return string
     */
    protected function buildFcmPayload(PayloadMessage $message)
    {
        return json_encode([
            'notification' =>
                [
                    'title' => $message->title,
                    'body' => $message->message,
                    'notification_count' => $message->badges,
                    'sound' => 'tingeling.wav'
                ],
            'data' => $message->data
        ]);
    }

    /**
     * Ghi log notification và trả về số notification chưa đọc
     *
     * @param UnicastMessage $data
     * @return mixed
     */
    protected function addLogNotification(UnicastMessage $data)
    {
        // TODO: Insert db
        $mNotify = new StaffNotificationTable();
        $oNotify = $mNotify->addNotify(
            $data->staff_id,
            $data->title,
            $data->message,
            $data->detail_id,
            $data->avatar
        );

        // TODO: so luong tin nhan chua doc
        $badges = $mNotify->countIsNewNotify($data->staff_id);

        return [$oNotify->staff_notification_id, $badges];
    }

    /**
     * Gọi push notification trong danh sách
     *
     * @param $userList
     * @param BroadcastMessage $data
     */
    protected function pushList($userList, BroadcastMessage $data)
    {
        // message
        $data = $data->toArray();
        unset($data['schedule']);
        $message = new UnicastMessage($data);

        // tiến hành gửi
        foreach ($userList as $user) {
            $message->staff_id = $user->staff_id;
            $job = new UnicastUserJob($message);
            dispatch($job);
        }
    }
}

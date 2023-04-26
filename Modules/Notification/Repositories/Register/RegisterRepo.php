<?php
namespace Modules\Notification\Repositories\Register;

use Aws\Sns\SnsClient;
use Carbon\Carbon;
use Modules\Notification\Models\StaffDeviceTable;

/**
 * Class RegisterRepo
 * @package Modules\Notification\Repositories\Register
 * @author DaiDP
 * @since Aug, 2020
 */
class RegisterRepo implements RegisterInterface
{
    /**
     * @var SnsClient
     */
    protected $client;
    protected $_mDeviceToken;

    /**
     * RegisterRepo constructor.
     */
    public function __construct(StaffDeviceTable $mDeviceToken)
    {
        $this->client = app('aws')->createClient('sns');
        $this->_mDeviceToken = $mDeviceToken;
    }

    /**
     * Đăng ký device token
     *
     * @param RegisterTokenMessage $platform
     * @param $deviceToken
     * @return string|null
     */
    public function register($message)
    {
        $switchDb = switch_brand_db($message->tenant_id);

        if(!$switchDb) return;

        try{
            // Lấy token dựa vào user, imei
            $tokenInfo = $this->_mDeviceToken->getInfo($message->staff_id, $message->imei, $message->platform);

            // kiểm tra token có thay đổi không. không thì kết thúc
            if ($tokenInfo && $tokenInfo->token == $message->token) {
                $tokenInfo->last_access = Carbon::now();
                $tokenInfo->date_modified = Carbon::now();
                $tokenInfo->is_actived = 1;
                $tokenInfo->save();
//                return;
            }

            // Tiến hành đăng ký
            $endpoint = $this->_register($message->platform, $message->token);

            // cập nhật lại db
            if ($tokenInfo) {
                $this->_mDeviceToken->updateToken($tokenInfo->staff_device_id, $message->token, $endpoint);
            }
            else {
                $this->_mDeviceToken->addDevice($message->toArray(), $endpoint);
            }
        }catch (\Exception $ex){

            echo "<pre>";
            print_r($ex->getMessage());
            echo "</pre>";
            die;
        }
    }

    private function _register($platform, $deviceToken)
    {
        $config = config('services.aws_sns');
        // TODO: Đăng ký platform endpoint
        $result = $this->client->createPlatformEndpoint([
            'PlatformApplicationArn' => $platform == 'android' ? $config['android_arn'] : $config['ios_arn'],
            'Token' => $deviceToken,
        ]);

        // TODO: Lấy ra endpoint ARN. dùng chuỗi này để push notification
        $endpointArn = $result['EndpointArn'] ?? null;

        // TODO: Kiểm tra null, không đăng ký được thì ngừng, để tránh lỗi phía sau
        if (is_null($endpointArn)) {
            return $endpointArn;
        }

        // TODO: enable endpoint nếu bị disable do khong push dc
        $this->client->setEndpointAttributes([
            'EndpointArn' => $endpointArn,
            'Attributes'  => ['Enabled' => 'true'],
        ]);

        // TODO: subscriber broadcast topic
        $this->subscriberTopic($endpointArn, $config['topic_arn']);

        return $endpointArn;
    }


    /**
     * Subscriber topic
     *
     * @param $endpointArn
     * @param $topic
     * @return string|null
     */
    public function subscriberTopic($endpointArn, $topic)
    {
        // TODO: kiểm tra endpoint arn
        $result = $this->client->subscribe([
            'Endpoint' => $endpointArn,
            'Protocol' => 'application',
            'TopicArn' => $topic
        ]);

        return $result['SubscriptionArn'] ?? null;
    }
}

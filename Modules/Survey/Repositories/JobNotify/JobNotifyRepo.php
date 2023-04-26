<?php

/**
 * Created by PhpStorm.
 * User: nguyenngocson
 * Date: 13/04/2021
 * Time: 14:29
 */

namespace Modules\Survey\Repositories\JobNotify;

use GuzzleHttp\Client;
use Modules\Survey\Models\SurveyAnswerTable;
use Modules\Notification\Entities\UnicastMessage;
use Modules\Survey\Models\NotificationDetailTable;
use Modules\Survey\Models\StaffNotificationDetailTable;
use Modules\Survey\Models\NotificationTemplateAutoTable;
use Modules\Survey\Models\StaffNotificationTemplateAutoTable;
use Modules\Notification\Repositories\PushNotification\PushNotificationRepo;

class JobNotifyRepo implements JobNotifyRepoInterface
{

        /**
         * hàm gửi thông báo khảo sát
         * @param array $listUser 
         * @param object $survey
         * @param string $typeUser
         * @param string $typeNotifi
         * @param string $tenant_id
         */

        public function sendNotifi(
                $listUser,
                $survey,
                $typeUser,
                $typeNotifi,
                $tenant_id
        ) {
                $switchDb = switch_brand_db($tenant_id);
                if (!$switchDb) return;
                // lấy ra cấu hình notifi //
                $mNotificationTemplateAuto = new NotificationTemplateAutoTable();
                $mNotificationDetail = new NotificationDetailTable();
                $itemNotifi = $mNotificationTemplateAuto->getItemByKey($typeNotifi);
                $titleNotifi = $itemNotifi->title;
                $messageNotifi = $itemNotifi->message;
                $nameSurvey = $survey->survey_name;
                $surveyId = $survey->survey_id;
                $messageNotifiConvert = str_replace("[survey_name]", $nameSurvey, $messageNotifi);
                $tenantId = session()->get('idTenant');
                foreach ($listUser as $user) {
                        $actionParam = [
                                "survey_id" => $surveyId,
                                "user_id" => $user
                        ];
                        $dataInsert = [
                                "content" => "<p style='font-size:14px'>$nameSurvey</p>",
                                "action_name" => __('Xem chi tiết'),
                                "action" => __('survey_detail'),
                                "action_params" => json_encode($actionParam)
                        ];
                        $itemNotifiDetail = $mNotificationDetail->create($dataInsert);
                        $dataSendNotifi =  [
                                'tenant_id' => $tenantId,
                                'user_id'  => $user,
                                'detail_id' => $itemNotifiDetail->notification_detail_id,
                                'title' => $titleNotifi,
                                'message' => $messageNotifiConvert,
                                'avatar' => '',
                                'schedule' => '',
                                'notification_type' => 'default',
                                'background' => null
                        ];
                        $message = new UnicastMessage($dataSendNotifi);
                        $notiDetailRepo = app()->get(PushNotificationRepo::class);
                        $notiDetailRepo->unicast($message);
                }
        }

        /**
         * gửi thông báo kết quả khảo sát tính điểm
         * @param $sessionAnswer
         */

        public function sendNotifiResutlPoint(SurveyAnswerTable $sessionAnswer, $tenantId)
        {
                $switchDb = switch_brand_db($tenantId);
                if (!$switchDb) return;
                // cập nhật lại phiên trả lời //
                $sessionAnswer->update([
                        'is_notifi' => SurveyAnswerTable::IS_NOTIFI
                ]);
                // lấy ra cấu hình notifi //
                $mNotificationTemplateAuto = new NotificationTemplateAutoTable();
                $mNotificationDetail = new NotificationDetailTable();
                $typeNotifi = $mNotificationTemplateAuto::KEY_NOTIFI_SURVEY_POINT;
                $itemNotifi = $mNotificationTemplateAuto->getItemByKey($typeNotifi);
                $titleNotifi = $itemNotifi->title;
                $messageNotifi = $itemNotifi->message;
                $nameSurvey = $sessionAnswer->survey_name;
                $surveyId = $sessionAnswer->survey_id;
                $user = $sessionAnswer->user_id;
                $totalPoint = $sessionAnswer->total_point;
                $messageNotifiConvert = str_replace("[survey_name]", $nameSurvey, $messageNotifi);
                $actionParam = [
                        "survey_id" => $surveyId,
                        "user_id" => $user,
                        'total_point' => $totalPoint
                ];
                $dataInsert = [
                        "content" => "<p style='font-size:14px'>$nameSurvey</p>",
                        "action_name" => __('Xem chi tiết'),
                        "action" => __('survey_detail'),
                        "action_params" => json_encode($actionParam)
                ];
                $itemNotifiDetail = $mNotificationDetail->create($dataInsert);
                $dataSendNotifi =  [
                        'tenant_id' => $tenantId,
                        'user_id'  => $user,
                        'detail_id' => $itemNotifiDetail->notification_detail_id,
                        'title' => $titleNotifi,
                        'message' => $messageNotifiConvert,
                        'avatar' => '',
                        'schedule' => '',
                        'notification_type' => 'default',
                        'background' => null
                ];
                $message = new UnicastMessage($dataSendNotifi);
                $notiDetailRepo = app()->get(PushNotificationRepo::class);
                $notiDetailRepo->unicast($message);
        }
}

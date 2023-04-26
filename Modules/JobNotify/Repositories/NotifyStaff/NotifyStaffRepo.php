<?php
/**
 * Created by PhpStorm.
 * User: nguyenngocson
 * Date: 09/06/2022
 * Time: 10:26
 */

namespace Modules\JobNotify\Repositories\NotifyStaff;


use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Modules\JobNotify\Models\ConfigStaffNotificationTable;
use Modules\JobNotify\Models\ConfigTable;
use Modules\JobNotify\Models\CustomerAppointmentTable;
use Modules\JobNotify\Models\CustomerServiceCardTable;
use Modules\JobNotify\Models\CustomerTable;
use Modules\JobNotify\Models\DeliveryHistoryTable;
use Modules\JobNotify\Models\MapRoleGroupStaffTable;
use Modules\JobNotify\Models\OrderDetailTable;
use Modules\JobNotify\Models\OrderTable;
use Modules\JobNotify\Models\ResetRankLogTable;
use Modules\JobNotify\Models\StaffNotificationDetailTable;
use Modules\JobNotify\Models\StaffNotificationReceiverTable;
use Modules\JobNotify\Models\StaffNotificationTable;
use Modules\JobNotify\Models\StaffTable;
use Modules\JobNotify\Models\TicketAcceptanceTable;
use Modules\JobNotify\Models\TicketRequestMaterialTable;
use Modules\JobNotify\Models\TicketTable;
use Modules\Notification\Entities\UnicastMessage;
use Modules\Notification\Jobs\UnicastUserJob;

class NotifyStaffRepo implements NotifyStaffRepoInterface
{
    /**
     * Gửi thông báo
     *
     * @param $input
     * @return mixed|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function saveLogNotifyStaff($input)
    {
        try {
            $key = [
                'ticket_assign','ticket_edit','ticket_operater','request_material_create','request_material_remove','request_material_approve',
                'request_material_reject','acceptance_create','acceptance_edit','ticket_image','request_material_edit','ticket_finish_operater',
                'ticket_finish_processor','ticket_close_operater','ticket_close_processor','request_material_create_staff','ticket_rating'
            ];
            $mConfig = app()->get(ConfigStaffNotificationTable::class);

            //Kiểm tra config notification
            $config = $mConfig->getInfo($input['key']);

            if ($config == null) {
//                throw new NotificationRepoException(NotificationRepoException::SEND_NOTIFICATION_FAILED);
                return '';
            }
            //Replace thông báo
            $replaceData = $this->replaceContentStaffNotify($config, $input['customer_id'], $input['object_id']);
            //Gắn chi nhánh thông báo
            $replaceData['dataNotification']['branch_id'] = isset($input['branch_id']) ? $input['branch_id'] : '';

            //Kiểm tra send type
            if ($config['send_type'] == "immediately") {
                //Insert thông báo
                if (in_array($input['key'],$key)){
                    $this->insertStaffNotifyLogTicket($replaceData['dataNotificationDetail'], $replaceData['dataNotification'], $input['customer_id']);
                } else {
                    $this->insertStaffNotifyLog($replaceData['dataNotificationDetail'], $replaceData['dataNotification'], $input);
                }
            } else if ($config['send_type'] == "in_time") {
                $dateCheck = Carbon::createFromFormat('d/m/Y H:i', $replaceData['dateCheck'])->format('d/m/Y H:i');
                //Kiểm tra thời gian hiện tại bằng với thời gian config thì gửi
                if ($dateCheck == Carbon::now()->format('d/m/Y H:i')) {
                    //Insert thông báo
                    if (in_array($input['key'],$key)){
                        $this->insertStaffNotifyLogTicket($replaceData['dataNotificationDetail'], $replaceData['dataNotification'],$input['customer_id']);
                    } else {
                        $this->insertStaffNotifyLog($replaceData['dataNotificationDetail'], $replaceData['dataNotification'], $input);
                    }
                }
            } else {
                $dateCheck = $replaceData['dateCheck'];
                if ($config['send_type'] == "before") {
                    $dateCheck = Carbon::createFromFormat('d/m/Y H:i', $replaceData['dateCheck'])->sub($config['value'], $config['schedule_unit'])->format('d/m/Y H:i');
                } else if ($config['send_type'] == "after") {
                    $dateCheck = Carbon::createFromFormat('d/m/Y H:i', $replaceData['dateCheck'])->add($config['value'], $config['schedule_unit'])->format('d/m/Y H:i');
                }
                //Kiểm tra thời gian hiện tại bằng với thời gian config thì gửi
                if ($dateCheck == Carbon::now()->format('d/m/Y H:i')) {
                    //Insert thông báo
                    if (in_array($input['key'],$key)){
                        $this->insertStaffNotifyLogTicket($replaceData['dataNotificationDetail'], $replaceData['dataNotification'],$input['customer_id']);
                    } else {
                        $this->insertStaffNotifyLog($replaceData['dataNotificationDetail'], $replaceData['dataNotification'], $input);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::info($e->getMessage() . $e->getFile() . $e->getLine());
        }
    }

    /**
     * Replace nội dung thông báo nhân viên
     *
     * @param $config
     * @param $userId
     * @param $objectId
     * @return array|void
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function replaceContentStaffNotify($config, $userId, $objectId)
    {
        try {
            $mOrder = app()->get(OrderTable::class);
            $mOrderDetail = app()->get(OrderDetailTable::class);
            $mCustomerAppointment = app()->get(CustomerAppointmentTable::class);
            $mCustomerServiceCard = app()->get(CustomerServiceCardTable::class);
            $mCustomer = app()->get(CustomerTable::class);
            $mResetRank = app()->get(ResetRankLogTable::class);
            $mDeliveryHistory = app()->get(DeliveryHistoryTable::class);
            $mConfig = app()->get(ConfigTable::class);

            $mTicket = new TicketTable();
            $mTicketAcceptance = new TicketAcceptanceTable();
            $mTicketRequestMaterial = new TicketRequestMaterialTable();

            //Data
            $dataNotificationDetail = [];
            $dataNotification = [];

            switch ($config['key']) {
                //Hủy lịch hẹn
                case 'appointment_C':
                    //Nhắc lịch
                case 'appointment_R':
                    //Xác nhận lịch hẹn
                case 'appointment_A':
                    //Cập nhật lịch hẹn
                case 'appointment_U':
                    //Lịch hẹn mới
                case 'appointment_W':
                    //Thông tin lịch hẹn
                    $info = $mCustomerAppointment->getInfo($objectId);
                    $message = str_replace(
                        [
                            '[branch_name]',
                            '[date]',
                            '[time]',
                            '[appointment_code]'
                        ],
                        [
                            $info['branch_name'],
                            Carbon::parse($info['date'])->format('d/m/Y'),
                            Carbon::parse($info['time'])->format('H:i'),
                            $info['customer_appointment_code']
                        ], $config['message']);
                    $content = str_replace(
                        [
                            '[branch_name]',
                            '[date]',
                            '[time]',
                            '[appointment_code]'
                        ],
                        [
                            $info['branch_name'],
                            Carbon::parse($info['date'])->format('d/m/Y'),
                            Carbon::parse($info['time'])->format('H:i'),
                            $info['customer_appointment_code']
                        ], $config['detail_content']);
                    $params = str_replace(
                        [
                            '[:customer_appointment_id]',
                            '[:user_id]',
                            '[:brand_url]',
                            '[:brand_name]',
                            '[:brand_id]'
                        ],
                        [
                            $info['customer_appointment_id'],
                            $userId,
                            '',
                            '',
                            0
                        ], $config['detail_action_params']);
                    //Data insert
                    $dataNotificationDetail = [
                        'background' => $config['detail_background'],
                        'content' => $content,
                        'action_name' => $config['detail_action_name'],
                        'action' => $config['detail_action'],
                        'action_params' => $params
                    ];
                    $dataNotification = [
                        'user_id' => $userId,
                        'notification_avatar' => $config['avatar'],
                        'notification_title' => $config['title'],
                        'notification_message' => $message
                    ];

                    $dateCheck = Carbon::now()->format('d/m/Y H:i');

                    if ($config['send_type'] == "in_time") {
                        $dateCheck = Carbon::now()->format("d/m/Y") . $config['value'];
                    } else if ($config['send_type'] == "before" || $config['send_type'] == "after") {
                        $dateCheck = Carbon::parse($info['date'] . $info['time'])->format('d/m/Y H:i');
                    }

                    return [
                        'dataNotificationDetail' => $dataNotificationDetail,
                        'dataNotification' => $dataNotification,
                        'dateCheck' => $dateCheck
                    ];
                    break;
                //Chúc mừng sinh nhật
                case 'customer_birthday':
                    $info = $mCustomer->getInfo($userId);
                    $message = $config['message'];
                    $content = $config['detail_content'];
                    $params = $config['detail_action_params'];
                    //Data insert
                    $dataNotificationDetail = [
                        'background' => $config['detail_background'],
                        'content' => $content,
                        'action_name' => $config['detail_action_name'],
                        'action' => $config['detail_action'],
                        'action_params' => $params
                    ];
                    $dataNotification = [
                        'user_id' => $userId,
                        'notification_avatar' => $config['avatar'],
                        'notification_title' => $config['title'],
                        'notification_message' => $message
                    ];

                    $dateCheck = Carbon::now()->format('d/m/Y H:i');

                    if ($config['send_type'] == "in_time") {
                        $dateCheck = Carbon::now()->format("d/m/Y") . $config['value'];
                    } else if ($config['send_type'] == "before" || $config['send_type'] == "after") {
                        $dateCheck = Carbon::parse($info['created_at'])->format('d/m/Y H:i');
                    }

                    return [
                        'dataNotificationDetail' => $dataNotificationDetail,
                        'dataNotification' => $dataNotification,
                        'dateCheck' => $dateCheck
                    ];
                    break;
                //Thăng hạng thành viên
                case 'customer_ranking':
                    $info = $mResetRank->getLastResetRank($userId);

                    if ($info['point_new'] <= $info['point_old']) {
                        break;
                    }
                    $message = str_replace(['[name]'], [$info['rank_new_name']], $config['message']);
                    $content = str_replace(['[name]'], [$info['rank_new_name']], $config['detail_content']);
                    $params = str_replace(
                        [
                            '[name]',
                        ],
                        [
                            $info['rank_new_name'],
                        ], $config['detail_action_params']);
                    //Data insert
                    $dataNotificationDetail = [
                        'background' => $config['detail_background'],
                        'content' => $content,
                        'action_name' => $config['detail_action_name'],
                        'action' => $config['detail_action'],
                        'action_params' => $params
                    ];
                    $dataNotification = [
                        'user_id' => $userId,
                        'notification_avatar' => $config['avatar'],
                        'notification_title' => $config['title'],
                        'notification_message' => $message
                    ];

                    $dateCheck = Carbon::now()->format('d/m/Y H:i');

                    if ($config['send_type'] == "in_time") {
                        $dateCheck = Carbon::now()->format("d/m/Y") . $config['value'];
                    } else if ($config['send_type'] == "before" || $config['send_type'] == "after") {
                        $dateCheck = Carbon::parse($info['created_at'])->format('d/m/Y H:i');
                    }

                    return [
                        'dataNotificationDetail' => $dataNotificationDetail,
                        'dataNotification' => $dataNotification,
                        'dateCheck' => $dateCheck
                    ];
                    break;
                //Khách hàng mới
                case 'customer_W':
                    $info = $mCustomer->getInfo($userId);
                    $message = str_replace(['[name]'], [$info['full_name']], $config['message']);
                    $content = str_replace(['[name]'], [$info['full_name']], $config['detail_content']);
                    $params = $config['detail_action_params'];
                    //Data insert
                    $dataNotificationDetail = [
                        'background' => $config['detail_background'],
                        'content' => $content,
                        'action_name' => $config['detail_action_name'],
                        'action' => $config['detail_action'],
                        'action_params' => $params
                    ];
                    $dataNotification = [
                        'user_id' => $userId,
                        'notification_avatar' => $config['avatar'],
                        'notification_title' => $config['title'],
                        'notification_message' => $message
                    ];

                    $dateCheck = Carbon::now()->format('d/m/Y H:i');

                    if ($config['send_type'] == "in_time") {
                        $dateCheck = Carbon::now()->format("d/m/Y") . $config['value'];
                    } else if ($config['send_type'] == "before" && $config['send_type'] == "after") {
                        $dateCheck = Carbon::parse($info['created_at'])->format('d/m/Y H:i');
                    }

                    return [
                        'dataNotificationDetail' => $dataNotificationDetail,
                        'dataNotification' => $dataNotification,
                        'dateCheck' => $dateCheck
                    ];

                    break;
                //Đơn hàng đang giao hàng
                case 'order_status_D':
                    //Đơn hàng đã giao hàng
                case 'order_status_I':
                    //Đơn hàng đã trã hàng
                case 'order_status_B':
                    //Đơn hàng đã thanh toán
                case 'order_status_S':
                    //Hủy đơn hàng
                case 'order_status_C':
                    //Xác nhận đơn hàng
                case 'order_status_A':
                    //Đơn hàng mới
                case 'order_status_W':
                    //Lấy cấu hình số lẻ
                    $decimalNumber = intval($mConfig->getConfig('decimal_number')['value']);
                    //Thông tin đơn hàng
                    $info = $mOrder->getInfo($objectId, $userId);
                    //Đếm số lượng sp/dv/thẻ dv của đơn hàng
                    $totalProduct = $mOrderDetail->sumTotalProduct($info['order_id']);

                    $message = str_replace(
                        [
                            '[order_code]',
                            '[customer_name]',
                            '[total_product]',
                            '[total_amount]'
                        ],
                        [
                            $info['order_code'],
                            $info['customer_name'],
                            intval($totalProduct['total_quantity']),
                            number_format($info['amount'], $decimalNumber ? $decimalNumber : 0)
                        ], $config['message']);
                    $content = str_replace(
                        [
                            '[order_code]',
                            '[customer_name]',
                            '[total_product]',
                            '[total_amount]'
                        ],
                        [
                            $info['order_code'],
                            $info['customer_name'],
                            intval($totalProduct['total_quantity']),
                            number_format($info['amount'], $decimalNumber ? $decimalNumber : 0)
                        ], $config['detail_content']);

                    $params = str_replace(
                        [
                            '[:order_id]',
                            '[:user_id]',
                            '[:brand_url]',
                            '[:brand_name]',
                            '[:brand_id]'
                        ],
                        [
                            $info['order_id'],
                            $info['customer_id'],
                            '',
                            '',
                            0
                        ], $config['detail_action_params']);
                    //Data insert
                    $dataNotificationDetail = [
                        'background' => $config['detail_background'],
                        'content' => $content,
                        'action_name' => $config['detail_action_name'],
                        'action' => $config['detail_action'],
                        'action_params' => $params
                    ];
                    $dataNotification = [
                        'user_id' => $info['customer_id'],
                        'notification_avatar' => $config['avatar'],
                        'notification_title' => $config['title'],
                        'notification_message' => $message
                    ];

                    $dateCheck = Carbon::now()->format('d/m/Y H:i');

                    if ($config['send_type'] == "in_time") {
                        $dateCheck = Carbon::now()->format("d/m/Y") . $config['value'];
                    } else if ($config['send_type'] == "before" || $config['send_type'] == "after") {
                        $dateCheck = Carbon::parse($info['created_at'])->format('d/m/Y H:i');
                    }

                    return [
                        'dataNotificationDetail' => $dataNotificationDetail,
                        'dataNotification' => $dataNotification,
                        'dateCheck' => $dateCheck
                    ];
                    break;
                //Thẻ dịch vụ sắp hết hạn sử dụng
                case 'service_card_nearly_expired':
                    //Thẻ dịch vụ hết hạn sử dụng
                case 'service_card_expired':
                    $info = $mCustomerServiceCard->getInfo($objectId);
                    $message = str_replace(['[name]', '[expired_date]'], [$info['service_card_name'], Carbon::parse($info['expired_date'])->format('d/m/Y')], $config['message']);
                    $content = str_replace(['[name]', '[expired_date]'], [$info['service_card_name'], Carbon::parse($info['expired_date'])->format('d/m/Y')], $config['detail_content']);
                    $params = $config['detail_action_params'];
                    //Data insert
                    $dataNotificationDetail = [
                        'background' => $config['detail_background'],
                        'content' => $content,
                        'action_name' => $config['detail_action_name'],
                        'action' => $config['detail_action'],
                        'action_params' => $params
                    ];
                    $dataNotification = [
                        'user_id' => $userId,
                        'notification_avatar' => $config['avatar'],
                        'notification_title' => $config['title'],
                        'notification_message' => $message
                    ];

                    $dateCheck = Carbon::now()->format('d/m/Y H:i');

                    if ($config['send_type'] == "in_time") {
                        $dateCheck = Carbon::now()->format("d/m/Y") . $config['value'];
                    } else if ($config['send_type'] == "before" || $config['send_type'] == "after") {
                        $dateCheck = Carbon::parse($info['expired_date'])->format('d/m/Y H:i');
                    }

                    return [
                        'dataNotificationDetail' => $dataNotificationDetail,
                        'dataNotification' => $dataNotification,
                        'dateCheck' => $dateCheck
                    ];
                    break;
                //Thẻ dịch vụ hết số lần sử dụng
                case 'service_card_over_number_used':
                    $info = $mCustomerServiceCard->getInfo($objectId);
                    if ($info['number_using'] == $info['count_using']) {
                        $message = str_replace(['[name]'], [$info['service_card_name']], $config['message']);
                        $content = str_replace(['[name]'], [$info['service_card_name']], $config['detail_content']);
                        $params = $config['detail_action_params'];
                        //Data insert
                        $dataNotificationDetail = [
                            'background' => $config['detail_background'],
                            'content' => $content,
                            'action_name' => $config['detail_action_name'],
                            'action' => $config['detail_action'],
                            'action_params' => $params
                        ];
                        $dataNotification = [
                            'user_id' => $userId,
                            'notification_avatar' => $config['avatar'],
                            'notification_title' => $config['title'],
                            'notification_message' => $message
                        ];

                        $dateCheck = Carbon::now()->format('d/m/Y H:i');

                        if ($config['send_type'] == "in_time") {
                            $dateCheck = Carbon::now()->format("d/m/Y") . $config['value'];
                        } else if ($config['send_type'] == "before" || $config['send_type'] == "after") {
                            $dateCheck = Carbon::parse($info['created_at'])->format('d/m/Y H:i');
                        }

                        return [
                            'dataNotificationDetail' => $dataNotificationDetail,
                            'dataNotification' => $dataNotification,
                            'dateCheck' => $dateCheck
                        ];
                    } else {
                        break;
                    }
                    break;
                //Phiếu giao hàng mới
                case 'delivery_W';
                    $info = $mDeliveryHistory->getInfo($objectId);
                    $message = str_replace(['[delivery_history_code]'], [$info['delivery_history_code']], $config['message']);
                    $content = str_replace(['[delivery_history_code]'], [$info['delivery_history_code']], $config['detail_content']);
                    $params = str_replace(
                        [
                            '[:order_id]',
                            '[:delivery_history_id]',
                            '[:user_id]',
                            '[:brand_url]',
                            '[:brand_name]',
                            '[:brand_id]'
                        ],
                        [
                            $info['order_id'],
                            $info['delivery_history_id'],
                            $userId,
                            '',
                            '',
                            0
                        ], $config['detail_action_params']);

                    //Data insert
                    $dataNotificationDetail = [
                        'background' => $config['detail_background'],
                        'content' => $content,
                        'action_name' => $config['detail_action_name'],
                        'action' => $config['detail_action'],
                        'action_params' => $params
                    ];
                    $dataNotification = [
                        'user_id' => $userId,
                        'notification_avatar' => $config['avatar'],
                        'notification_title' => $config['title'],
                        'notification_message' => $message
                    ];

                    $dateCheck = Carbon::now()->format('d/m/Y H:i');

                    if ($config['send_type'] == "in_time") {
                        $dateCheck = Carbon::now()->format("d/m/Y") . $config['value'];
                    } else if ($config['send_type'] == "before" || $config['send_type'] == "after") {
                        $dateCheck = Carbon::parse($info['created_at'])->format('d/m/Y H:i');
                    }

                    return [
                        'dataNotificationDetail' => $dataNotificationDetail,
                        'dataNotification' => $dataNotification,
                        'dateCheck' => $dateCheck
                    ];
                    break;
                //                Ticket được phân công
                case 'ticket_assign';
                case 'ticket_edit';
                case 'ticket_operater';
                case 'ticket_image';
                    $info = $mTicket->getInfoNoti($objectId);
                    $message = str_replace(['[staff_name]','[ticket_code]'], [$info['full_name_updated'],$info['ticket_code']], $config['message']);
                    $content = str_replace(['[staff_name]','[ticket_code]'], [$info['full_name_updated'],$info['ticket_code']], $config['detail_content']);
                    $params = str_replace(
                        [
                            '[:ticket_id]'
                        ],
                        [
                            $info['ticket_id']
                        ], $config['detail_action_params']);

                    //Data insert
                    $dataNotificationDetail = [
                        'background' => $config['detail_background'],
                        'content' => $content,
                        'action_name' => $config['detail_action_name'],
                        'action' => $config['detail_action'],
                        'action_params' => $params
                    ];
                    $dataNotification = [
                        'user_id' => $userId,
                        'notification_avatar' => $config['avatar'],
                        'notification_title' => $config['title'],
                        'notification_message' => $message
                    ];

                    $dateCheck = Carbon::now()->format('d/m/Y H:i');

                    if ($config['send_type'] == "in_time") {
                        $dateCheck = Carbon::now()->format("d/m/Y") . $config['value'];
                    } else if ($config['send_type'] == "before" || $config['send_type'] == "after") {
                        $dateCheck = Carbon::parse($info['created_at'])->format('d/m/Y H:i');
                    }

                    return [
                        'dataNotificationDetail' => $dataNotificationDetail,
                        'dataNotification' => $dataNotification,
                        'dateCheck' => $dateCheck
                    ];
                    break;
                case 'request_material_create';
                case 'request_material_edit';
                case 'request_material_remove';
                case 'request_material_approve';
                case 'request_material_reject';
                case 'request_material_create_staff';
                    $info = $mTicketRequestMaterial->getDetailForNoti($objectId);
                    $message = str_replace(['[staff_name]','[ticket_request_material_code]','[ticket_code]'], [$info['full_name_updated'],$info['ticket_request_material_code'],$info['ticket_code']], $config['message']);
                    $content = str_replace(['[staff_name]','[ticket_request_material_code]','[ticket_code]'], [$info['full_name_updated'],$info['ticket_request_material_code'],$info['ticket_code']], $config['detail_content']);
                    $params = str_replace(
                        [
                            '[:ticket_id]',
                            '[:ticket_request_material_id]',
                        ],
                        [
                            $info['ticket_id'],
                            $info['ticket_request_material_id'],
                        ], $config['detail_action_params']);

                    //Data insert
                    $dataNotificationDetail = [
                        'background' => $config['detail_background'],
                        'content' => $content,
                        'action_name' => $config['detail_action_name'],
                        'action' => $config['detail_action'],
                        'action_params' => $params
                    ];
                    $dataNotification = [
                        'user_id' => $userId,
                        'notification_avatar' => $config['avatar'],
                        'notification_title' => $config['title'],
                        'notification_message' => $message
                    ];

                    $dateCheck = Carbon::now()->format('d/m/Y H:i');

                    if ($config['send_type'] == "in_time") {
                        $dateCheck = Carbon::now()->format("d/m/Y") . $config['value'];
                    } else if ($config['send_type'] == "before" || $config['send_type'] == "after") {
                        $dateCheck = Carbon::parse($info['created_at'])->format('d/m/Y H:i');
                    }

                    return [
                        'dataNotificationDetail' => $dataNotificationDetail,
                        'dataNotification' => $dataNotification,
                        'dateCheck' => $dateCheck
                    ];
                    break;
                case 'acceptance_create';
                case 'acceptance_edit';
                    $info = $mTicketAcceptance->getDetailForNoti($objectId);
                    $message = str_replace(['[staff_name]','[ticket_acceptance_code]','[ticket_code]'], [$info['full_name_updated'],$info['ticket_acceptance_code'],$info['ticket_code']], $config['message']);
                    $content = str_replace(['[staff_name]','[ticket_acceptance_code]','[ticket_code]'], [$info['full_name_updated'],$info['ticket_acceptance_code'],$info['ticket_code']], $config['detail_content']);
                    $params = str_replace(
                        [
                            '[:ticket_id]',
                            '[:ticket_acceptance_id]',
                        ],
                        [
                            $info['ticket_id'],
                            $info['ticket_acceptance_id'],
                        ], $config['detail_action_params']);

                    //Data insert
                    $dataNotificationDetail = [
                        'background' => $config['detail_background'],
                        'content' => $content,
                        'action_name' => $config['detail_action_name'],
                        'action' => $config['detail_action'],
                        'action_params' => $params
                    ];
                    $dataNotification = [
                        'user_id' => $userId,
                        'notification_avatar' => $config['avatar'],
                        'notification_title' => $config['title'],
                        'notification_message' => $message
                    ];

                    $dateCheck = Carbon::now()->format('d/m/Y H:i');

                    if ($config['send_type'] == "in_time") {
                        $dateCheck = Carbon::now()->format("d/m/Y") . $config['value'];
                    } else if ($config['send_type'] == "before" || $config['send_type'] == "after") {
                        $dateCheck = Carbon::parse($info['created_at'])->format('d/m/Y H:i');
                    }

                    return [
                        'dataNotificationDetail' => $dataNotificationDetail,
                        'dataNotification' => $dataNotification,
                        'dateCheck' => $dateCheck
                    ];
                    break;
                case 'ticket_finish_operater';
                case 'ticket_finish_processor';
                    $info = $mTicket->getInfoNoti($objectId);
                    $message = str_replace(['[staff_name]','[ticket_code]'], [$info['full_name_updated'],$info['ticket_code']], $config['message']);
                    $content = str_replace(['[staff_name]','[ticket_code]'], [$info['full_name_updated'],$info['ticket_code']], $config['detail_content']);
                    $params = str_replace(
                        [
                            '[:ticket_id]'
                        ],
                        [
                            $info['ticket_id']
                        ], $config['detail_action_params']);

                    //Data insert
                    $dataNotificationDetail = [
                        'background' => $config['detail_background'],
                        'content' => $content,
                        'action_name' => $config['detail_action_name'],
                        'action' => $config['detail_action'],
                        'action_params' => $params
                    ];
                    $dataNotification = [
                        'user_id' => $userId,
                        'notification_avatar' => $config['avatar'],
                        'notification_title' => $config['title'],
                        'notification_message' => $message
                    ];

                    $dateCheck = Carbon::now()->format('d/m/Y H:i');

                    if ($config['send_type'] == "in_time") {
                        $dateCheck = Carbon::now()->format("d/m/Y") . $config['value'];
                    } else if ($config['send_type'] == "before" || $config['send_type'] == "after") {
                        $dateCheck = Carbon::parse($info['created_at'])->format('d/m/Y H:i');
                    }

                    return [
                        'dataNotificationDetail' => $dataNotificationDetail,
                        'dataNotification' => $dataNotification,
                        'dateCheck' => $dateCheck
                    ];
                    break;
                case 'ticket_close_operater';
                case 'ticket_close_processor';
                    $info = $mTicket->getInfoNoti($objectId);
                    $message = str_replace(['[ticket_code]'], [$info['ticket_code']], $config['message']);
                    $content = str_replace(['[ticket_code]'], [$info['ticket_code']], $config['detail_content']);
                    $params = str_replace(
                        [
                            '[:ticket_id]'
                        ],
                        [
                            $info['ticket_id']
                        ], $config['detail_action_params']);

                    //Data insert
                    $dataNotificationDetail = [
                        'background' => $config['detail_background'],
                        'content' => $content,
                        'action_name' => $config['detail_action_name'],
                        'action' => $config['detail_action'],
                        'action_params' => $params
                    ];
                    $dataNotification = [
                        'user_id' => $userId,
                        'notification_avatar' => $config['avatar'],
                        'notification_title' => $config['title'],
                        'notification_message' => $message
                    ];

                    $dateCheck = Carbon::now()->format('d/m/Y H:i');

                    if ($config['send_type'] == "in_time") {
                        $dateCheck = Carbon::now()->format("d/m/Y") . $config['value'];
                    } else if ($config['send_type'] == "before" || $config['send_type'] == "after") {
                        $dateCheck = Carbon::parse($info['created_at'])->format('d/m/Y H:i');
                    }

                    return [
                        'dataNotificationDetail' => $dataNotificationDetail,
                        'dataNotification' => $dataNotification,
                        'dateCheck' => $dateCheck
                    ];
                    break;
                case 'ticket_rating';
                    $info = $mTicket->getInfoNoti($objectId);
                    $message = str_replace(['[staff_name]','[ticket_code]'], [$info['full_name_created'],$info['ticket_code']], $config['message']);
                    $content = str_replace(['[staff_name]','[ticket_code]'], [$info['full_name_created'],$info['ticket_code']], $config['detail_content']);
                    $params = str_replace(
                        [
                            '[:ticket_id]'
                        ],
                        [
                            $info['ticket_id']
                        ], $config['detail_action_params']);

                    //Data insert
                    $dataNotificationDetail = [
                        'background' => $config['detail_background'],
                        'content' => $content,
                        'action_name' => $config['detail_action_name'],
                        'action' => $config['detail_action'],
                        'action_params' => $params
                    ];
                    $dataNotification = [
                        'user_id' => $userId,
                        'notification_avatar' => $config['avatar'],
                        'notification_title' => $config['title'],
                        'notification_message' => $message
                    ];

                    $dateCheck = Carbon::now()->format('d/m/Y H:i');

                    if ($config['send_type'] == "in_time") {
                        $dateCheck = Carbon::now()->format("d/m/Y") . $config['value'];
                    } else if ($config['send_type'] == "before" || $config['send_type'] == "after") {
                        $dateCheck = Carbon::parse($info['created_at'])->format('d/m/Y H:i');
                    }

                    return [
                        'dataNotificationDetail' => $dataNotificationDetail,
                        'dataNotification' => $dataNotification,
                        'dateCheck' => $dateCheck
                    ];
                    break;
                default:
                    return [
                        'dataNotificationDetail' => $dataNotificationDetail,
                        'dataNotification' => $dataNotification
                    ];
                    break;
            }
        } catch (\Exception $e) {
            return;
        }
    }

    /**
     * Lưu log thông báo nhân viên
     *
     * @param $dataNotificationDetail
     * @param $dataNotification
     * @param $input
     * @return string
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function insertStaffNotifyLog($dataNotificationDetail, $dataNotification, $input)
    {
        try {
            $mNotificationDetail = app()->get(StaffNotificationDetailTable::class);
            $mReceiver = app()->get(StaffNotificationReceiverTable::class);
            $mMapRoleGroup = app()->get(MapRoleGroupStaffTable::class);

            $arrRoleGroup = [];

            //Lấy nhóm quyền được nhận notify
            $getReceiver = $mReceiver->getReceiverByKey($input['key']);

            if (count($getReceiver) > 0) {
                foreach ($getReceiver as $v) {
                    $arrRoleGroup [] = $v['role_group_id'];
                }
            }

            //Lấy ds nhân viên
            $getStaff = $mMapRoleGroup->getStaffByArrayRole($arrRoleGroup, $input['branch_id']);

            if (count($getStaff) > 0) {
                foreach ($getStaff as $v) {
                    //Insert notification detail
                    $idNotificationDetail = $mNotificationDetail->add($dataNotificationDetail);
                    //Push notification
                    $message = new UnicastMessage([
                        'tenant_id' => session()->get('idTenant'),
                        'staff_id' => $v['staff_id'],
                        'title' => $dataNotification['notification_title'],
                        'message' => $dataNotification['notification_message'],
                        'detail_id' => $idNotificationDetail,
                        'avatar' => $dataNotification['notification_avatar']
                    ]);
                    $job = new UnicastUserJob($message);
                    dispatch($job);
                }
            }
        } catch (\Exception $exception) {
            return '';
        }
    }

    /**
     * Push noti ticket
     * @param $dataNotificationDetail
     * @param $dataNotification
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function insertStaffNotifyLogTicket($dataNotificationDetail, $dataNotification, $staff_id){
        try {
            $mNotification = app()->get(StaffNotificationTable::class);
            $mNotificationDetail = app()->get(StaffNotificationDetailTable::class);

            $idNotificationDetail = $mNotificationDetail->add($dataNotificationDetail);
            //Push notification
            $message = new UnicastMessage([
                'tenant_id' => session()->get('idTenant'),
                'staff_id' => $staff_id,
                'title' => $dataNotification['notification_title'],
                'message' => $dataNotification['notification_message'],
                'detail_id' => $idNotificationDetail,
                'avatar' => $dataNotification['notification_avatar']
            ]);
            $job = new UnicastUserJob($message);
            dispatch($job);
        } catch (\Exception $exception) {
            return '';
        }
    }
}
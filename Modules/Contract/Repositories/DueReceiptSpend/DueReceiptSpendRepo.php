<?php
/**
 * Created by PhpStorm.
 * User: nguyenngocson
 * Date: 26/11/2021
 * Time: 10:05
 */

namespace Modules\Contract\Repositories\DueReceiptSpend;


use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Modules\Contract\Models\ContractCategoryRemindTable;
use Modules\Contract\Models\ContractFollowMapTable;
use Modules\Contract\Models\ContractRemindMapMethodTable;
use Modules\Contract\Models\ContractRemindMapReceiverTable;
use Modules\Contract\Models\ContractSignMapTable;
use Modules\Contract\Models\ContractStaffQueueTable;
use Modules\Contract\Models\ContractTable;
use Modules\Contract\Models\ContractTagMapTable;
use Modules\Contract\Models\StaffEmailLogTable;
use Modules\Contract\Models\StaffNotifyDetailTable;
use Modules\Contract\Models\StaffTable;

class DueReceiptSpendRepo implements DueReceiptSpendRepoInterface
{
    /**
     * Job lưu log nhắc nhở đến hạn thu - chi
     *
     * @return mixed|string
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function jobSaveLogDue()
    {
        try {
            $mContractRemind = app()->get(ContractCategoryRemindTable::class);

            //Lấy cấu hình nhắc nhở thu chi
            $getRemindDue = $mContractRemind->getRemindDue();

            if (count($getRemindDue) > 0) {
                foreach ($getRemindDue as $v) {
                    //Xử lý nhắc nhở đến hạn thu - chi
                    $this->handleDueReceiptSpend($v);
                }
            }
        } catch (\Exception $e) {
            Log::info($e->getMessage() . $e->getFile() . $e->getLine());
            return '';
        }
    }

    /**
     * Xử lý dữ liệu đến hạn thu - chi
     *
     * @param $input
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function handleDueReceiptSpend($input)
    {
        $mContract = app()->get(ContractTable::class);

        //Lấy ds hợp đồng theo loại
        $getContract = $mContract->getContractByCategory($input['contract_category_id']);

        if (count($getContract) > 0) {
            //Lấy ngày hiện tại
            $dateNow = Carbon::now()->format('Y-m-d');

            foreach ($getContract as $v) {
                //Lấy ngày so sánh
                $dateCompare = null;

                //Lấy cấu hình loại nhắc nhở
                switch ($input['compare_unit']) {
                    case 'sign_date':
                        //Ngày ký HĐ
                        if ($input['recipe'] == "<" && $v['sign_date'] != null) {
                            $dateCompare = $this->calculateDate($v['sign_date'], $input['unit'], $input['unit_value']);
                        } else if ($input['recipe'] == "=" && $v['sign_date'] != null) {
                            $dateCompare = $v['sign_date'];
                        }
                        break;
                    case 'effective_date':
                        //Ngày có hiệu lực
                        if ($input['recipe'] == "<" && $v['effective_date'] != null) {
                            $dateCompare = $this->calculateDate($v['effective_date'], $input['unit'], $input['unit_value']);
                        } else if ($input['recipe'] == "=" && $v['effective_date'] != null) {
                            $dateCompare = $v['effective_date'];
                        }
                        break;
                    case 'expired_date':
                        //Ngày hết hiệu lực
                        if ($input['recipe'] == "<" && $v['expired_date'] != null) {
                            $dateCompare = $this->calculateDate($v['expired_date'], $input['unit'], $input['unit_value']);
                        } else if ($input['recipe'] == "=" && $v['expired_date'] != null) {
                            $dateCompare = $v['expired_date'];
                        }
                        break;
                    case 'warranty_start_date';
                        //Ngày bắt đầu bảo hành
                        if ($input['recipe'] == "<" && $v['warranty_start_date'] != null) {
                            $dateCompare = $this->calculateDate($v['warranty_start_date'], $input['unit'], $input['unit_value']);
                        } else if ($input['recipe'] == "=" && $v['warranty_start_date'] != null) {
                            $dateCompare = $v['warranty_start_date'];
                        }
                        break;
                    case 'warranty_end_date';
                        //Ngày kết thúc bảo hành
                        if ($input['recipe'] == "<" && $v['warranty_end_date'] != null) {
                            $dateCompare = $this->calculateDate($v['warranty_end_date'], $input['unit'], $input['unit_value']);
                        } else if ($input['recipe'] == "=" && $v['warranty_end_date'] != null) {
                            $dateCompare = $v['warranty_end_date'];
                        }
                        break;
                    case 'expected_receive_date';
                        //Ngày dự kiến thu (Lấy từ cấu hình của dự kiến thu)

                        break;
                    case 'expected_spend_date';
                        //Ngày dự kiến chi

                        break;
                    case 'contract_due_date';
                        //Ngày sắp hết hạn HĐ (Ngày hết hiệu lực - Ngày cần gia hạn)
                        if ($v['expired_date'] != null) {
                            $dateCompare = Carbon::parse($v['expired_date'])->subDays(intval($v['number_day_renew']))->format('Y-m-d');
                        }

                        break;
                }

                if ($dateCompare == null || $dateNow != $dateCompare) {
                    continue;
                }

                $mRemindMapReceiver = app()->get(ContractRemindMapReceiverTable::class);
                $mRemindMapMethod = app()->get(ContractRemindMapMethodTable::class);

                $arrReceiver = [];

                //Lấy cấu hình người nhận
                $getReceiver = $mRemindMapReceiver->getReceiver($input['contract_category_remind_id']);

                if (count($getReceiver) > 0) {
                    foreach ($getReceiver as $v1) {
                        //Lấy tập người nhận
                        $arrReceiver = $this->getArrayReceiver($v, $v1['receiver_by'], $arrReceiver);
                    }

                }

                //Lấy hình thức gửi
                $getMethod = $mRemindMapMethod->getMethod($input['contract_category_remind_id']);

                if (count(array_unique($arrReceiver)) > 0 && count($getMethod) > 0) {
                    //Lấy nội dung gửi
                    $contentSend = $this->_replaceContent($v, $input['content']);

                    foreach ($getMethod as $v) {
                        if ($v['remind_method'] == "staff_notify") {
                            //Gửi thông báo
                            $this->_saveLogNotify($v, $arrReceiver, $contentSend, $input['title']);
                        } else if ($v['remind_method'] == "email") {
                            //Gửi email
                            $this->_saveLogEmail($v, $arrReceiver, $contentSend, $input['title']);
                        }
                    }
                }
            }
        }
    }

    /**
     * Tính ngày gửi
     *
     * @param $date
     * @param $unit
     * @param $unitValue
     * @return null|string
     */
    private function calculateDate($date, $unit, $unitValue)
    {
        $dateLast = null;

        switch ($unit) {
            case 'D':
                //Ngày
                $dateLast = Carbon::parse($date)->subDays($unitValue)->format('Y-m-d');
                break;
            case 'W':
                //Tuần
                $dateLast = Carbon::parse($date)->subWeeks($unitValue)->format('Y-m-d');
                break;
            case 'M':
                //Tháng
                $dateLast = Carbon::parse($date)->subMonths($unitValue)->format('Y-m-d');
                break;
            case 'Q':
                //Quý
                $dateLast = Carbon::parse($date)->subMonths($unitValue * 3)->format('Y-m-d');
                break;
            case 'Y':
                //Năm
                $dateLast = Carbon::parse($date)->subYears($unitValue)->format('Y-m-d');
                break;
        }

        return $dateLast;
    }

    /**
     * Lấy array người nhận theo từng case
     *
     * @param $infoContract
     * @param $receiverBy
     * @param $arrSendBy
     * @return array
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function getArrayReceiver($infoContract, $receiverBy, $arrSendBy)
    {
        switch ($receiverBy) {
            case 'performer_by';
                //Người thực hiện
                $arrSendBy [] = $infoContract['performer_by'];
                break;
            case 'sign_by';
                $mContractSignMap = app()->get(ContractSignMapTable::class);
                //Lấy người ký HĐ
                $getSignBy = $mContractSignMap->getSignMap($infoContract['contract_id']);

                if (count($getSignBy) > 0) {
                    foreach ($getSignBy as $v) {
                        $arrSendBy [] = $v['sign_by'];
                    }
                }
                break;
            case 'follow_by';
                $mContractFollowMap = app()->get(ContractFollowMapTable::class);
                //Lấy người theo dõi HĐ
                $getFollowMap = $mContractFollowMap->getFollowMap($infoContract['contract_id']);

                if (count($getFollowMap) > 0) {
                    foreach ($getFollowMap as $v) {
                        $arrSendBy [] = $v['follow_by'];
                    }
                }
                break;
            case 'created_by';
                $arrSendBy [] = $infoContract['created_by'];
                break;
            case 'updated_by';
                $arrSendBy [] = $infoContract['updated_by'];
                break;
        }

        return $arrSendBy;
    }

    /**
     * Replace nội dung gửi nhắc nhở
     *
     * @param $infoContract
     * @param $configContent
     * @return mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function _replaceContent($infoContract, $configContent)
    {
        $mContractSignMap = app()->get(ContractSignMapTable::class);
        $mContractFollowMap = app()->get(ContractFollowMapTable::class);
        $mContractTagMap = app()->get(ContractTagMapTable::class);

        $signBy = "";
        $followBy = "";
        $tag = "";

        //Lấy người ký HĐ
        $getSignBy = $mContractSignMap->getSignMap($infoContract['contract_id']);

        if (count($getSignBy) > 0) {
            foreach ($getSignBy as $k => $v) {
                $signBy .= $k + 1 < count($getSignBy) ? $v['sign_name'] . ', ' : $v['sign_name'];
            }
        }

        //Lấy người theo dõi
        $getFollowMap = $mContractFollowMap->getFollowMap($infoContract['contract_id']);

        if (count($getFollowMap) > 0) {
            foreach ($getFollowMap as $k => $v) {
                $followBy .= $k + 1 < count($getFollowMap) ? $v['follow_name'] . ', ' : $v['follow_name'];
            }
        }

        //Lấy tag HĐ
        $getTag = $mContractTagMap->getContractTag($infoContract['contract_id']);

        if (count($getTag) > 0) {
            foreach ($getTag as $k => $v) {
                $tag .= $k + 1 < count($getTag) ? $v['tag_name'] . ', ' : $v['tag_name'];
            }
        }

        //Nội dung gửi
        $message = str_replace(
            [
                '{contract_category_id}',
                '{contract_name}',
                '{sign_date}',
                '{performer_by}',
                '{effective_date}',
                '{sign_by}',
                '{contract_no}',
                '{expired_date}',
                '{follow_by}',
                '{tag}',
                '{content}',
                '{note}',
                '{warranty_start_date}',
                '{warranty_end_date}',
            ],
            [
                $infoContract['contract_category_name'],
                $infoContract['contract_name'],
                $infoContract['sign_date'] != null ? Carbon::parse($infoContract['sign_date'])->format('d/m/Y') : null,
                $infoContract['performer_name'] != null ? $infoContract['performer_name'] : null,
                $infoContract['effective_date'] != null ? Carbon::parse($infoContract['effective_date'])->format('d/m/Y') : null,
                $signBy,
                $infoContract['contract_no'] != null ? $infoContract['contract_no'] : null,
                $infoContract['expired_date'] != null ? Carbon::parse($infoContract['effective_date'])->format('d/m/Y') : null,
                $followBy,
                $tag,
                $infoContract['content'] != null ? $infoContract['content'] : null,
                $infoContract['note'] != null ? $infoContract['note'] : null,
                $infoContract['warranty_start_date'] != null ? Carbon::parse($infoContract['warranty_start_date'])->format('d/m/Y') : null,
                $infoContract['warranty_end_date'] != null ? Carbon::parse($infoContract['warranty_end_date'])->format('d/m/Y') : null
            ], $configContent);


        return $message;
    }

    /**
     * Lưu log notify
     *
     * @param $infoContract
     * @param $arrReceiver
     * @param $content
     * @param $title
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function _saveLogNotify($infoContract, $arrReceiver, $content, $title)
    {
        // data notification detail
        $dataNotificationDetail = [
            'tenant_id' => '',
            'background' => '',
            'content' => $content,
            'action_name' => 'Xem chi tiết',
            'action' => 'contract_detail',
            'action_params' => '{"contract_id":"' . $infoContract['contract_id'] . '"}',
            'is_brand' => 1,
            'created_at' => Carbon::now()->format('Y-m-d H:i:s')
        ];

        // data contract staff queue
        $dataContractStaffQueue = [
            'tenant_id' => '',
            'contract_id' => $infoContract['contract_id'],
            'staff_notification_title' => $title,
            'staff_notification_message' => $content,
            'send_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'is_actived' => 1,
            'is_send' => 0,
            'created_at' => Carbon::now()->format('Y-m-d H:i:s')
        ];

        if (count($arrReceiver) > 0) {
            $mStaffNotifyDetail = app()->get(StaffNotifyDetailTable::class);
            $mContractStaffQueue = app()->get(ContractStaffQueueTable::class);

            //Thêm chi tiết thông báo
            $idNotifyDetail = $mStaffNotifyDetail->add($dataNotificationDetail);

            foreach ($arrReceiver as $v) {
                $dataContractStaffQueue['staff_notification_detail_id'] = $idNotifyDetail;
                $dataContractStaffQueue['staff_id'] = $v;
                //Thêm staff queue
                $mContractStaffQueue->add($dataContractStaffQueue);
            }
        }
    }

    /**
     * Lưu log email
     *
     * @param $infoContract
     * @param $arrReceiver
     * @param $content
     * @param $title
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function _saveLogEmail($infoContract, $arrReceiver, $content, $title)
    {
        // data staff email log
        $dataStaffEmailLog = [
            'email_type' => 'contract_notify',
            'email_subject' => $title,
            'email_from' => '',
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ];

        if (count($arrReceiver) > 0) {
            $mStaff = app()->get(StaffTable::class);
            $mStaffEmailLog = app()->get(StaffEmailLogTable::class);

            foreach ($arrReceiver as $v) {
                //Lấy thông tin nhân viên
                $getStaff = $mStaff->getInfo($v);

                if ($getStaff != null && $getStaff['email'] != null) {
                    $dataStaffEmailLog['email_to'] = $getStaff['email'];
                    $dataStaffEmailLog['email_params'] = json_encode([
                        'content' => $content,
                        'title' => $title
                    ]);
                    //Lưu log email
                    $mStaffEmailLog->add($dataStaffEmailLog);
                }
            }
        }
    }
}
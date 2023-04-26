<?php
/**
 * Created by PhpStorm.
 * User: nguyenngocson
 * Date: 26/11/2021
 * Time: 15:51
 */

namespace Modules\Contract\Repositories\ContractExpired;


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

class ContractExpiredRepo implements ContractExpiredRepoInterface
{
    /**
     * Job nhắc nhhở HĐ đến hạn
     *
     * @return mixed|string
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function jobSaveLogContractExpired()
    {
        try {
            $mContractRemind = app()->get(ContractCategoryRemindTable::class);

            //Lấy cấu hình nhắc nhở đến hạn HĐ
            $getContractExpired = $mContractRemind->getRemindDueDate();

            if (count($getContractExpired) > 0) {
                foreach ($getContractExpired as $v) {
                    //Xử lý nhắc nhở đến hạn HĐ
                    $this->handleContractExpired($v);
                }
            }
        } catch (\Exception $e) {
            Log::info($e->getMessage() . $e->getFile() . $e->getLine());
            return '';
        }
    }

    /**
     * Xử lý dữ liệu đến hạn HĐ
     *
     * @param $input
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function handleContractExpired($input)
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

                //Tính ngày sắp hết hạn HĐ
                if ($v['expired_date'] != null) {
                    $dateCompare = Carbon::parse($v['expired_date'])->format('Y-m-d');
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
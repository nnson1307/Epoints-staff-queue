<?php
/**
 * Created by PhpStorm.
 * User: nguyenngocson
 * Date: 24/11/2021
 * Time: 16:00
 */

namespace Modules\Contract\Repositories\ExpectedRevenue;


use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Modules\Contract\Models\ContractExpectedRevenueLogTable;
use Modules\Contract\Models\ContractExpectedRevenueTable;
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

class ExpectedRevenueRepo implements ExpectedRevenueRepoInterface
{
    /**
     * Job lưu log nhắc nhở thu - chi
     *
     * @return mixed|string
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function jobSaveLogExpectedRevenue()
    {
        try {
            $mExpectedRevenue = app()->get(ContractExpectedRevenueTable::class);

            //Lấy dự kiến thu - chi của HĐ
            $getExpectedRevenue = $mExpectedRevenue->getExpectedRevenue();

            if (count($getExpectedRevenue) > 0) {
                foreach ($getExpectedRevenue as $v) {
                    if ($v['type'] == 'receipt') {
                        //Dự kiến thu
                        $this->handleExpectedReceipt($v);
                    } else if ($v['type'] == 'spend') {
                        //Dự kiến chi
                        $this->handleExpectedSpend($v);
                    }
                }
            }

        } catch (\Exception $e) {
            Log::info($e->getMessage() . $e->getFile() . $e->getLine());
            return '';
        }
    }

    /**
     * Xử lý dự kiến thu
     *
     * @param $input
     * @return string
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function handleExpectedReceipt($input)
    {
        $mContract = app()->get(ContractTable::class);
        //Lấy ngày hiện tại
        $dateNow = Carbon::now()->format('Y-m-d');
        //Lấy ngày so sánh
        $dateCompare = null;
        //Lấy thông tin HĐ
        $info = $mContract->getInfo($input['contract_id']);

        if ($info == null) {
            return '';
        }

        //Lấy cấu hình loại nhắc nhở
        switch ($input['compare_unit']) {
            case 'sign_date':
                //Ngày ký HĐ
                if ($input['recipe'] == "<" && $info['sign_date'] != null) {
                    $dateCompare = $this->calculateDate($info['sign_date'], $input['unit'], $input['unit_value']);
                } else if ($input['recipe'] == "=" && $info['sign_date'] != null) {
                    $dateCompare = $info['sign_date'];
                }
                break;
            case 'effective_date':
                //Ngày có hiệu lực
                if ($input['recipe'] == "<" && $info['effective_date'] != null) {
                    $dateCompare = $this->calculateDate($info['effective_date'], $input['unit'], $input['unit_value']);
                } else if ($input['recipe'] == "=" && $info['effective_date'] != null) {
                    $dateCompare = $info['effective_date'];
                }
                break;
            case 'expired_date':
                //Ngày hết hiệu lực
                if ($input['recipe'] == "<" && $info['expired_date'] != null) {
                    $dateCompare = $this->calculateDate($info['expired_date'], $input['unit'], $input['unit_value']);
                } else if ($input['recipe'] == "=" && $info['expired_date'] != null) {
                    $dateCompare = $info['expired_date'];
                }
                break;
            case 'warranty_start_date';
                //Ngày bắt đầu bảo hành
                if ($input['recipe'] == "<" && $info['warranty_start_date'] != null) {
                    $dateCompare = $this->calculateDate($info['warranty_start_date'], $input['unit'], $input['unit_value']);
                } else if ($input['recipe'] == "=" && $info['warranty_start_date'] != null) {
                    $dateCompare = $info['warranty_start_date'];
                }
                break;
            case 'warranty_end_date';
                //Ngày kết thúc bảo hành
                if ($input['recipe'] == "<" && $info['warranty_end_date'] != null) {
                    $dateCompare = $this->calculateDate($info['warranty_end_date'], $input['unit'], $input['unit_value']);
                } else if ($input['recipe'] == "=" && $info['warranty_end_date'] != null) {
                    $dateCompare = $info['warranty_end_date'];
                }
                break;
            case 'expected_receive_date';
                //Ngày dự kiến thu (Lấy từ cấu hình của dự kiến thu)
                $mRevenueLog = app()->get(ContractExpectedRevenueLogTable::class);
                //Lấy log ngày dự kiến thu
                $getLog = $mRevenueLog->getLog($input['contract_expected_revenue_id']);

                if (count($getLog) > 0) {
                    foreach ($getLog as $v) {
                        if (Carbon::parse($v['date_send'])->format('Y-m-d') == $dateNow) {
                            $dateCompare = Carbon::parse($v['date_send'])->format('Y-m-d');
                        }
                    }
                }
                break;
            case 'expected_spend_date';
                //Ngày dự kiến chi
                return '';

                break;
            case 'contract_due_date';
                //Ngày sắp hết hạn HĐ (Ngày hết hiệu lực - Ngày cần gia hạn)
                if ($info['expired_date'] != null) {
                    $dateCompare = Carbon::parse($info['expired_date'])->subDays(intval($info['number_day_renew']))->format('Y-m-d');
                }

                break;
        }

        if ($dateCompare == null || $dateNow != $dateCompare) {
            return '';
        }

        $mRemindMapReceiver = app()->get(ContractRemindMapReceiverTable::class);
        $mRemindMapMethod = app()->get(ContractRemindMapMethodTable::class);

        $arrReceiver = [];

        //Lấy cấu hình người nhận
        $getReceiver = $mRemindMapReceiver->getReceiver($input['contract_category_remind_id']);

        if (count($getReceiver) > 0) {
            foreach ($getReceiver as $v) {
                //Lấy tập người nhận
                $arrReceiver = $this->getArrayReceiver($info, $v['receiver_by'], $arrReceiver);
            }

        }

        //Lấy hình thức gửi
        $getMethod = $mRemindMapMethod->getMethod($input['contract_category_remind_id']);

        if (count(array_unique($arrReceiver)) > 0 && count($getMethod) > 0) {
            //Lấy nội dung gửi
            $contentSend = $this->_replaceContent($info, $input['content']);

            foreach ($getMethod as $v) {
                if ($v['remind_method'] == "staff_notify") {
                    //Gửi thông báo
                    $this->_saveLogNotify($info, $arrReceiver, $contentSend, $input['title']);
                } else if ($v['remind_method'] == "email") {
                    //Gửi email
                    $this->_saveLogEmail($info, $arrReceiver, $contentSend, $input['title']);
                }
            }
        }
    }

    /**
     * Xử lý dự kiến chi
     *
     * @param $input
     * @return string
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function handleExpectedSpend($input)
    {
        $mContract = app()->get(ContractTable::class);
        //Lấy ngày hiện tại
        $dateNow = Carbon::now()->format('Y-m-d');
        //Lấy ngày so sánh
        $dateCompare = null;
        //Lấy thông tin HĐ
        $info = $mContract->getInfo($input['contract_id']);

        if ($info == null) {
            return '';
        }

        //Lấy cấu hình loại nhắc nhở
        switch ($input['compare_unit']) {
            case 'sign_date':
                //Ngày ký HĐ
                if ($input['recipe'] == "<" && $info['sign_date'] != null) {
                    $dateCompare = $this->calculateDate($info['sign_date'], $input['unit'], $input['unit_value']);
                } else if ($input['recipe'] == "=" && $info['sign_date'] != null) {
                    $dateCompare = $info['sign_date'];
                }
                break;
            case 'effective_date':
                //Ngày có hiệu lực
                if ($input['recipe'] == "<" && $info['effective_date'] != null) {
                    $dateCompare = $this->calculateDate($info['effective_date'], $input['unit'], $input['unit_value']);
                } else if ($input['recipe'] == "=" && $info['effective_date'] != null) {
                    $dateCompare = $info['effective_date'];
                }
                break;
            case 'expired_date':
                //Ngày hết hiệu lực
                if ($input['recipe'] == "<" && $info['expired_date'] != null) {
                    $dateCompare = $this->calculateDate($info['expired_date'], $input['unit'], $input['unit_value']);
                } else if ($input['recipe'] == "=" && $info['expired_date'] != null) {
                    $dateCompare = $info['expired_date'];
                }
                break;
            case 'warranty_start_date';
                //Ngày bắt đầu bảo hành
                if ($input['recipe'] == "<" && $info['warranty_start_date'] != null) {
                    $dateCompare = $this->calculateDate($info['warranty_start_date'], $input['unit'], $input['unit_value']);
                } else if ($input['recipe'] == "=" && $info['warranty_start_date'] != null) {
                    $dateCompare = $info['warranty_start_date'];
                }
                break;
            case 'warranty_end_date';
                //Ngày kết thúc bảo hành
                if ($input['recipe'] == "<" && $info['warranty_end_date'] != null) {
                    $dateCompare = $this->calculateDate($info['warranty_end_date'], $input['unit'], $input['unit_value']);
                } else if ($input['recipe'] == "=" && $info['warranty_end_date'] != null) {
                    $dateCompare = $info['warranty_end_date'];
                }
                break;
            case 'expected_receive_date';
                //Ngày dự kiến thu (Lấy từ cấu hình của dự kiến thu)
                return '';

                break;
            case 'expected_spend_date';
                //Ngày dự kiến chi (Lấy từ cấu hình của dự kiến chi)
                $mRevenueLog = app()->get(ContractExpectedRevenueLogTable::class);
                //Lấy log ngày dự kiến thu
                $getLog = $mRevenueLog->getLog($input['contract_expected_revenue_id']);

                if (count($getLog) > 0) {
                    foreach ($getLog as $v) {
                        if (Carbon::parse($v['date_send'])->format('Y-m-d') == $dateNow) {
                            $dateCompare = Carbon::parse($v['date_send'])->format('Y-m-d');
                        }
                    }
                }

                break;
            case 'contract_due_date';
                //Ngày sắp hết hạn HĐ (Ngày hết hiệu lực - Ngày cần gia hạn)
                if ($info['expired_date'] != null) {
                    $dateCompare = Carbon::parse($info['expired_date'])->subDays(intval($info['number_day_renew']))->format('Y-m-d');
                }

                break;
        }

        if ($dateCompare == null || $dateNow != $dateCompare) {
            return '';
        }

        $mRemindMapReceiver = app()->get(ContractRemindMapReceiverTable::class);
        $mRemindMapMethod = app()->get(ContractRemindMapMethodTable::class);

        $arrReceiver = [];

        //Lấy cấu hình người nhận
        $getReceiver = $mRemindMapReceiver->getReceiver($input['contract_category_remind_id']);

        if (count($getReceiver) > 0) {
            foreach ($getReceiver as $v) {
                //Lấy tập người nhận
                $arrReceiver = $this->getArrayReceiver($info, $v['receiver_by'], $arrReceiver);
            }

        }

        //Lấy hình thức gửi
        $getMethod = $mRemindMapMethod->getMethod($input['contract_category_remind_id']);

        if (count(array_unique($arrReceiver)) > 0 && count($getMethod) > 0) {
            //Lấy nội dung gửi
            $contentSend = $this->_replaceContent($info, $input['content']);

            foreach ($getMethod as $v) {
                if ($v['remind_method'] == "staff_notify") {
                    //Gửi thông báo
                    $this->_saveLogNotify($info, $arrReceiver, $contentSend, $input['title']);
                } else if ($v['remind_method'] == "email") {
                    //Gửi email
                    $this->_saveLogEmail($info, $arrReceiver, $contentSend, $input['title']);
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
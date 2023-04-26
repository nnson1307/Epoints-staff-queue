<?php

namespace Modules\Commission\Repositories\StaffCommission;

use Carbon\Carbon;
use Modules\Commission\Models\CommissionAllocationTable;
use Modules\Commission\Models\CommissionConfigTable;
use Modules\Commission\Models\CommissionObjectMapTable;
use Modules\Commission\Models\ContractAnnexTable;
use Modules\Commission\Models\ContractMapOrderTable;
use Modules\Commission\Models\ContractTable;
use Modules\Commission\Models\KpiNoteDetailTable;
use Modules\Commission\Models\OrderTable;
use Modules\Commission\Models\ReceiptTable;
use Modules\Commission\Models\StaffCommissionEveryDayObjectTable;
use Modules\Commission\Models\StaffCommissionEveryDayTable;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class StaffCommissionRepo implements StaffCommissionRepoInterface
{
    const PAY_SUCCESS = "paysuccess";

    /**
     * Tính hoa hồng cho nhân viên
     *
     * @return mixed|void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function calculateStaffCommission()
    {
        try {
            $mAllocation = app()->get(CommissionAllocationTable::class);

            //Lấy ngày tính hoa hồng
            $date = Carbon::now()->subDays(5)->format('Y-m-d');
            //Lấy hoa hồng được phân bổ cho nhân viên
            $getAllocation = $mAllocation->getStaffAllocation();

            if (count($getAllocation) > 0) {
                foreach ($getAllocation as $v) {
                    $arrNumber = [];

                    $scopeObjectId = null;

                    switch ($v['commission_scope']) {
                        case 'personal':
                            $scopeObjectId = $v['staff_id'];
                            break;
                        case 'group';
                            $scopeObjectId = $v['team_id'];
                            break;
                        case 'branch':
                            $scopeObjectId = $v['branch_id'];
                            break;
                        case 'department':
                            $scopeObjectId = $v['department_id'];
                            break;
                    }

                    switch ($v['commission_type']) {
                        case 'order':
                            //Tính theo đơn hàng
                            $arrNumber = $this->typeOrder($v, $date, $scopeObjectId);

                            break;
                        case 'kpi':
                            //Tính theo kpi
                            $arrNumber = $this->typeKpi($v, $date, $v['commission_scope'], $scopeObjectId);

                            break;
                        case 'contract':
                            //Tính theo hợp đồng
                            $arrNumber = $this->typeContract($v, $date, $scopeObjectId);

                            break;
                    }

                    switch ($v['commission_calc_by']) {
                        case 0:
                            //Tính hoa hồng theo hạng mức
                            $this->_calculateCommissionByLevel($date, $v, $arrNumber);
                            break;
                        case 1:
                            //Tính hoa hồng theo bậc thang
                            $this->_calculateCommissionByStep($date, $v, $arrNumber);
                            break;
                    }

                }
            }
        } catch (\Exception $e) {
            dd($e->getMessage() . $e->getLine());
            return '';
        }
    }

    /**
     * Lấy giá trị theo loại đơn hàng
     *
     * @param $input
     * @param $date
     * @param $scopeObjectId
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function typeOrder($infoCommission, $date, $scopeObjectId)
    {
        $mOrder = app()->get(OrderTable::class);
        $mObjectMap = app()->get(CommissionObjectMapTable::class);

        $arrNumber = [];
        $arrObjectId = [];

        //Lấy hàng hoá áp dụng
        if ($infoCommission['order_commission_object_type'] == 'object') {
            $getObjectMap = $mObjectMap->getObjectMap($infoCommission['commission_id']);

            if (count($getObjectMap) > 0) {
                foreach ($getObjectMap as $v) {
                    $arrObjectId [] = $v['object_id'];
                }
            }
        }

        switch ($infoCommission['order_commission_calc_by']) {
            case 'paid-not-ship':
                //Từng phiếu thu ko bao gồm phí vận chuyển (Tạm ẩn)
                break;
            case 'paid-ship':
                //Từng phiếu thu bao gồm phí vận chuyển
                $mReceipt = app()->get(ReceiptTable::class);

                //lấy phiếu thu trong ngày
                $getReceipt = $mReceipt->getReceiptByDate(
                    $date,
                    $infoCommission['commission_scope'],
                    $scopeObjectId,
                    $infoCommission['order_commission_type'],
                    $arrObjectId
                );

                if (count($getReceipt) > 0) {
                    foreach ($getReceipt as $v) {
                        $arrNumber [] = [
                            'number_value' => floatval($v['amount_paid']),
                            'object' => [
                                [
                                    'object_type' => 'order',
                                    'object_id' => $v['order_id']
                                ]
                            ]
                        ];
                    }
                }

                break;

            case 'total-paid-not-ship':
                //Tất cả phiếu thu ko bao gồm phí vận chuyển (Tạm ẩn)

                break;
            case 'total-paid-ship':
                //Tất cả phiếu thu bao gồm phí vận chuyển

                $mOrder = app()->get(OrderTable::class);

                //Lấy đơn hàng đã hoàn thành
                $getOrder = $mOrder->getOrderSuccessByDate(
                    $date,
                    $infoCommission['commission_scope'],
                    $scopeObjectId,
                    $infoCommission['order_commission_type'],
                    $arrObjectId
                );

                if (count($getOrder) > 0) {
                    foreach ($getOrder as $v) {
                        $arrNumber [] = [
                            'number_value' => floatval($v['amount']),
                            'object' => [
                                [
                                    'object_type' => 'order',
                                    'object_id' => $v['order_id']
                                ]
                            ]
                        ];
                    }
                }

                break;
        }

        return $arrNumber;
    }

    /**
     * Lấy giá trị theo loại kpi
     *
     * @param $infoCommission
     * @param $date
     * @param $scope
     * @param $scopeObjectId
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function typeKpi($infoCommission, $date, $scope, $scopeObjectId)
    {
        $arrNumber = [];

        $kpiCriteriaType = "";

        switch ($scope) {
            case 'personal':
                $kpiCriteriaType = "S";
                break;
            case 'group':
                $kpiCriteriaType = "T";
                break;
            case 'branch':
                $kpiCriteriaType = "B";
                break;
            case 'department':
                $kpiCriteriaType = "D";
                break;
        }

        $mKpiNoteDetail = app()->get(KpiNoteDetailTable::class);

        //Lấy bảng tính hoa hồng theo tiêu chí
        $getKpi = $mKpiNoteDetail->getKpiClosing($kpiCriteriaType, $scopeObjectId, $infoCommission['kpi_commission_calc_by']);

        if (count($getKpi) > 0) {
            foreach ($getKpi as $v) {
                //Cập nhật lại kpi này đã tính hoa hồng
                $mKpiNoteDetail->edit([
                    'is_calculate_commission' => 1
                ], $v['kpi_note_detail_id']);

                $arrNumber [] = [
                    'number_value' => floatval($v['original_total_percent']),
                    'object' => [
                        [
                            'object_type' => 'kpi',
                            'object_id' => $v['kpi_criteria_id'],
                        ]
                    ]
                ];
            }
        }

        return $arrNumber;
    }

    /**
     * Lấy giá trị theo loại hợp đồng
     *
     * @param $infoCommission
     * @param $date
     * @param $scopeObjectId
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function typeContract($infoCommission, $date, $scopeObjectId)
    {
        $arrContract = [];
        $arrNumber = [];
        $dataContract = [];

        $mOrder = app()->get(OrderTable::class);
        $mReceipt = app()->get(ReceiptTable::class);

        $arrContractFrom = [];

        switch ($infoCommission['contract_commission_condition']) {
            //Lấy hợp đồng (mới, tái kí, gia hạn)
            case 'all':
                $arrContractFrom = ['new', 'renew'];
                break;
            //Lấy hợp đồng mới
            case 'new':
                $arrContractFrom = ['new'];
                break;
            //Lấy hợp đồng tái kí
            case 'renew':
                $arrContractFrom = ['renew'];
                break;
        }


        switch ($infoCommission['contract_commission_calc_by']) {
            //Tổng số hợp đồng đã thanh toán
            case 'all-paid':
                //Doanh thu hợp đồng đã thanh toán
            case 'paid-revenue':
                $annex = [];

                //Lấy doanh thu hợp hồng (mới, tái kí)
                $contract = $mOrder->getOrderContractSuccessByDate(
                    $date,
                    $infoCommission['commission_scope'],
                    $scopeObjectId,
                    $infoCommission['contract_commission_type'],
                    $arrContractFrom,
                    $infoCommission['contract_commission_apply'])->toArray();

                if ($infoCommission['contract_commission_condition'] == 'all') {
                    //Lấy doanh thu hợp hồng gia hạn
                    $annex = $mOrder->getOrderContractAnnexSuccessByDate(
                        $date,
                        $infoCommission['commission_scope'],
                        $scopeObjectId,
                        $infoCommission['contract_commission_type'],
                        $arrContractFrom,
                        $infoCommission['contract_commission_apply'])->toArray();
                }

                $arrContract = array_merge($contract, $annex);

                break;
            //Doanh thu hợp đồng thanh toan từng phần
            case 'half-paid-revenue':
                $annex = [];

                //Lấy phiếu thu của hợp đồng (mới, tái kí)
                $contract = $mReceipt->getReceiptContractByDate(
                    $date,
                    $infoCommission['commission_scope'],
                    $scopeObjectId,
                    $infoCommission['contract_commission_type'],
                    $arrContractFrom,
                    $infoCommission['contract_commission_apply'])->toArray();

                if ($infoCommission['contract_commission_condition'] == 'all') {
                    //Lấy phiếu thu của hợp đồng gia hạn
                    $annex = $mReceipt->getReceiptContractAnnexByDate(
                        $date,
                        $infoCommission['commission_scope'],
                        $scopeObjectId,
                        $infoCommission['contract_commission_type'],
                        $arrContractFrom,
                        $infoCommission['contract_commission_apply'])->toArray();
                }

                $arrContract = array_merge($contract, $annex);

                break;
        }

        if (count($arrContract) > 0) {
            foreach ($arrContract as $v) {
                //Validate thời hạn hợp đồng
                if ($infoCommission['contract_commission_operation'] != 'no_limit' && $v['effective_date'] != null && $v['expired_date'] != null) {
                    $effectiveDate = Carbon::parse($v['effective_date']);
                    $expiredDate = Carbon::parse($v['expired_date']);

                    //Lấy khoảng cách tháng của thời hạn hợp đồng
                    $diff = $effectiveDate->diffInMonths($expiredDate);

                    switch ($infoCommission['contract_commission_operation']) {
                        case '>':
                            if ($diff > $infoCommission['contract_commission_time']) {
                                $dataContract [] = $v;
                            }

                            break;
                        case '<':
                            if ($diff < $infoCommission['contract_commission_time']) {
                                $dataContract [] = $v;
                            }

                            break;
                        case '=':
                            if ($diff == $infoCommission['contract_commission_time']) {
                                $dataContract [] = $v;
                            }

                            break;
                    }
                } else {
                    $dataContract [] = $v;
                }
            }
        }

        $numberPaid = 0;
        $numberPaidObject = [];

        if (count($dataContract) > 0) {
            foreach ($dataContract as $v) {
                $numberPaid++;

                $numberPaidObject [] = [
                    'object_type' => 'contract',
                    'object_id' => $v['contract_id'],
                ];

                if (in_array($infoCommission['contract_commission_calc_by'], ['paid-revenue', 'half-paid-revenue'])) {
                    $arrNumber [] = [
                        'number_value' => floatval($v['amount']),
                        'object' => [
                            [
                                'object_type' => 'contract',
                                'object_id' => $v['contract_id'],
                            ]
                        ]
                    ];
                }
            }
        }

        switch ($infoCommission['contract_commission_calc_by']) {
            case 'all-paid':
                //Tổng số hợp đồng đã thanh toán
                $arrNumber [] = [
                    'number_value' => $numberPaid,
                    'object' => $numberPaidObject
                ];

                break;
            case 'all-half-paid':
                //Tổng số hợp đồng thanh toán từng phần
                $arrNumber [] = [
                    'number_value' => $numberPaid,
                    'object' => $numberPaidObject
                ];

                break;
        }

        return $arrNumber;
    }

    /**
     * Tính hoa hồng theo hạng mức
     *
     * @param $date
     * @param $infoCommission
     * @param $arrNumber
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function _calculateCommissionByLevel($date, $infoCommission, $arrNumber)
    {
        if (count($arrNumber) > 0) {
            $mCommissionEveryDay = app()->get(StaffCommissionEveryDayTable::class);
            $mCommissionEveryDayObject = app()->get(StaffCommissionEveryDayObjectTable::class);
            $mConfig = app()->get(CommissionConfigTable::class);

            foreach ($arrNumber as $v) {
                //Lấy cấu hình điều kiện
                $getConfig = $mConfig->getConfigByLevel($infoCommission['commission_id'], $v['number_value']);

                if ($getConfig != null) {
                    $commissionValue = 0;

                    switch ($getConfig['config_operation']) {
                        case 0:
                            //Theo VNĐ
                            $commissionValue = $getConfig['commission_value'];
                            break;
                        case 1:
                            //Theo %
                            $commissionValue = ($v['number_value'] / 100) * $getConfig['commission_value'];
                            break;
                    }

                    //Insert hoa hồng hàng ngày
                    $everyDayId = $mCommissionEveryDay->insertGetId([
                        'commission_id' => $infoCommission['commission_id'],
                        'staff_id' => $infoCommission['staff_id'],
                        'number_value' => $v['number_value'],
                        'commission_money' => $commissionValue * $infoCommission['commission_coefficient'],  //Nhân với hệ số hoa hồng
                        'coefficient' => $infoCommission['commission_coefficient'],
                        'date' => $date,
                        'day' => Carbon::parse($date)->format('d'),
                        'week' => Carbon::parse($date)->isoWeek,
                        'month' => Carbon::parse($date)->format('m'),
                        'year' => Carbon::parse($date)->format('Y'),
                        'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                        'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
                    ]);

                    $dataObject = [];

                    if (count($v['object']) > 0) {
                        foreach ($v['object'] as $v1) {
                            $dataObject [] = [
                                'staff_commission_every_day_id' => $everyDayId,
                                'object_type' => $v1['object_type'],
                                'object_id' => $v1['object_id'],
                            ];
                        }
                    }

                    //Insert đối tượng của hoa hồng
                    $mCommissionEveryDayObject->insert($dataObject);
                }
            }
        }
    }

    /**
     * Tính hoa hồng theo bậc thang
     *
     * @param $date
     * @param $infoCommission
     * @param $arrNumber
     * @return void
     */
    private function _calculateCommissionByStep($date, $infoCommission, $arrNumber)
    {
        if (count($arrNumber) > 0) {
            $mCommissionEveryDay = app()->get(StaffCommissionEveryDayTable::class);
            $mCommissionEveryDayObject = app()->get(StaffCommissionEveryDayObjectTable::class);
            $mConfig = app()->get(CommissionConfigTable::class);

            foreach ($arrNumber as $v) {
                //Lấy cấu hình điều kiện
                $getConfig = $mConfig->getConfigByStep($infoCommission['commission_id'], $v['number_value']);

                if (count($getConfig) > 0) {
                    $commissionValue = 0;

                    foreach ($getConfig as $k1 => $v1) {
                        switch ($v1['config_operation']) {
                            case 0:
                                //Theo VNĐ
                                $commissionValue += $v1['commission_value'];
                                break;
                            case 1:
                                //Theo %
                                if ($v1['max_value'] != null) {
                                    $commissionValue += (($v1['max_value'] - $v1['min_value']) / 100) * $v1['commission_value'];
                                } else {
                                    $commissionValue += ($v['number_value'] / 100) * $v1['commission_value'];
                                }
                                break;
                        }
                    }

                    //Insert hoa hồng hàng ngày
                    $everyDayId = $mCommissionEveryDay->insertGetId([
                        'commission_id' => $infoCommission['commission_id'],
                        'staff_id' => $infoCommission['staff_id'],
                        'number_value' => $v['number_value'],
                        'commission_money' => $commissionValue * $infoCommission['commission_coefficient'],  //Nhân với hệ số hoa hồng
                        'coefficient' => $infoCommission['commission_coefficient'],
                        'date' => $date,
                        'day' => Carbon::parse($date)->format('d'),
                        'week' => Carbon::parse($date)->isoWeek,
                        'month' => Carbon::parse($date)->format('m'),
                        'year' => Carbon::parse($date)->format('Y'),
                        'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                        'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
                    ]);

                    $dataObject = [];

                    if (count($v['object']) > 0) {
                        foreach ($v['object'] as $v2) {
                            $dataObject [] = [
                                'staff_commission_every_day_id' => $everyDayId,
                                'object_type' => $v2['object_type'],
                                'object_id' => $v2['object_id'],
                            ];
                        }
                    }

                    //Insert đối tượng của hoa hồng
                    $mCommissionEveryDayObject->insert($dataObject);
                }
            }
        }
    }
}
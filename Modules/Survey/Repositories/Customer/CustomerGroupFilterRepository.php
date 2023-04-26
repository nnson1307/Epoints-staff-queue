<?php

/**
 * Created by PhpStorm.
 * User: USER
 * Date: 10/23/2019
 * Time: 4:02 PM
 */

namespace Modules\Survey\Repositories\Customer;

use Carbon\Carbon;
use Box\Spout\Common\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Box\Spout\Reader\ReaderFactory;
use Illuminate\Support\Facades\Auth;
use Modules\Survey\Models\OrderTable;
use Modules\Survey\Models\ReceiptTable;
use Modules\Survey\Models\ServiceTable;
use Modules\Survey\Models\CustomerTable;
use Modules\Survey\Models\MemberLevelTable;
use Modules\Survey\Models\ServiceCardTable;
use Modules\Survey\Models\ProductChildTable;
use Modules\Survey\Models\CustomerAppointmentTable;
use Modules\Survey\Models\CustomerGroupDetailTable;
use Modules\Survey\Models\CustomerGroupFilterTable;
use Modules\Survey\Models\CustomerGroupConditionTable;
use Modules\Survey\Models\CustomerGroupDefineDetailTable;
use function Symfony\Component\Console\Tests\Command\createClosure;

class CustomerGroupFilterRepository implements CustomerGroupFilterRepositoryInterface
{
    protected $customer;
    protected $customerGroupCondition;
    protected $customerGroupDefineDetail;
    protected $customerGroupDetail;
    protected $customerGroupFilter;
    protected $service;
    protected $serviceCard;
    protected $productChild;
    protected $customerAppointment;
    protected $order;
    protected $memberLevel;
    protected $receipt;

    public function __construct(
        CustomerTable $customer,
        CustomerGroupConditionTable $customerGroupCondition,
        CustomerGroupDefineDetailTable $customerGroupDefineDetail,
        CustomerGroupFilterTable $customerGroupFilter,
        CustomerGroupDetailTable $customerGroupDetail,
        ServiceTable $service,
        ServiceCardTable $serviceCard,
        ProductChildTable $productChild,
        CustomerAppointmentTable $customerAppointment,
        OrderTable $order,
        MemberLevelTable $memberLevel,
        ReceiptTable $receipt
    ) {
        $this->receipt = $receipt;
        $this->memberLevel = $memberLevel;
        $this->customer = $customer;
        $this->customerGroupCondition = $customerGroupCondition;
        $this->customerGroupDefineDetail = $customerGroupDefineDetail;
        $this->customerGroupDetail = $customerGroupDetail;
        $this->customerGroupFilter = $customerGroupFilter;
        $this->service = $service;
        $this->serviceCard = $serviceCard;
        $this->productChild = $productChild;
        $this->customerAppointment = $customerAppointment;
        $this->order = $order;
    }

    /**
     * Danh sách nhóm
     *
     * @param array $filters
     *
     * @return mixed
     */
    public function list(array $filters = [])
    {
        return $this->customerGroupFilter->getList($filters);
    }

    /**
     * Thêm khách hàng bằng file excel
     * @param $file
     * @param $arrayPhoneExist
     * @return \Illuminate\Http\JsonResponse
     * @throws \Box\Spout\Common\Exception\IOException
     * @throws \Box\Spout\Common\Exception\UnsupportedTypeException
     * @throws \Box\Spout\Reader\Exception\ReaderNotOpenedException
     */
    public function importExcel(
        $file,
        $arrayPhoneExist
    ) {

        if (isset($file)) {
            $total = 0;
            $success = 0;
            $fail = 0;
            $arrayTemp = [];
            $typeFileExcel = $file->getClientOriginalExtension();
            if ($typeFileExcel == "xlsx" || $typeFileExcel == "csv") {
                $reader = ReaderFactory::create(Type::XLSX);
                $reader->open($file);
                foreach ($reader->getSheetIterator() as $sheet) {
                    foreach ($sheet->getRowIterator() as $key => $row) {
                        if ($key > 1) {
                            if (isset($row[1])) {
                                $total += 1;
                                if (!in_array(
                                    $row[1],
                                    $arrayPhoneExist
                                )) {
                                    $user = $this->customer->getCusPhone2($row[1]);
                                    if ($user != null) {
                                        $success += 1;
                                        $arrayTemp[] = $user['customer_id'];
                                    } else {
                                        $fail += 1;
                                    }
                                } else {
                                    $fail += 1;
                                }
                            }
                        } else {
                            $flag = false;
                            if (
                                isset($row[0])
                                && isset($row[1])
                            ) {
                                if (
                                    $row[0] == 'STT'
                                    && $row[1] == 'SỐ ĐIỆN THOẠI'
                                ) {
                                    $flag = true;
                                }
                            }
                            if ($flag == false) {
                                return response()->json(
                                    [
                                        'success' => 10,
                                        'message' => ''
                                    ]
                                );
                            }
                        }
                    }
                }
                $result['total'] = $total;
                $result['success'] = $success;
                $result['fail'] = $fail;
                $result['arrayPhone'] = $arrayTemp;

                $reader->close();
                return $result;
            }
            return response()->json(
                [
                    'success' => 1,
                    'message' => ''
                ]
            );
        }
    }

    /**
     * Tìm kiếm khách hàng
     * @param array $data
     * @return string
     * @throws \Throwable
     */
    public function searchWhereInUser(array $data = [])
    {
        if (isset($data['arrayUser'])) {
            if (count($data['arrayUser']) == 0) {
                $data['arrayUser'][] = "099999999999999";
            }

            //Tìm kiếm trong mycore.
            $filters['arrayUser'] = $data['arrayUser'];
            $filters['keyword_customers$phone1'] = isset($data['phone'])
                ? $data['phone'] : '';
            $filters['keyword_customers$full_name'] = isset($data['fullName'])
                ? $data['fullName'] : '';
            $filters['customers$is_actived'] = isset($data['isActive'])
                ? $data['isActive'] : '';
            $filters['page'] = (int)($data['page'] ?? 1);
            $page = $filters['page'];
            $list = $this->customer->getListSearch($filters);
            $view = view(
                'admin::customer-group-filter.user-define.partial.tr-user',
                [
                    'list' => $list,
                    'page' => $page
                ]
            )->render();
            return $view;
        } else {
            $view = view(
                'user::user-group-notification.user-define.partial.tr-user',
                []
            )->render();
            return $view;
        }
    }

    /**
     * Tìm kiếm toàn bộ user.
     * @param array $filters
     * @return array
     * @throws \Throwable
     */
    public function searchAllCustomer(array $filters = [])
    {
        $filter['keyword_customers$phone1'] = $filters['phone'];
        $filter['keyword_customers$full_name'] = $filters['fullName'];
        $filter['customers$is_actived'] = $filters['isActive'];
        $filter['page'] = (int) ($filters['page'] ?? 1);
        $list = $this->customer->getListSearch($filter);
        //render view.
        $view = view(
            'admin::customer-group-filter.user-define.partial.tr-user-2',
            [
                'list' => $list,
            ]
        )->render();

        $arrayPhone = [];
        $filter['perpage'] = 100000;
        $listAll = $this->customer->getListSearch($filter);
        foreach ($listAll as $item) {
            $arrayPhone[] = $item['customer_id'];
        }
        $result = [
            'view'       => $view,
            'arrayPhone' => $arrayPhone,
        ];


        return $result;
    }

    public function addCustomerGroupDefine(array $data = [])
    {
        if (isset($data['arrayAccount'])) {
            if (count($data['arrayAccount']) == 0) {
                $data['arrayAccount'][] = "099999999999999";
            }
            $filters['arrayUser'] = $data['arrayAccount'];
            $filters['arrayUser'] = $data['arrayAccount'];
            $filters['page'] = (int)($data['page'] ?? 1);
            $filters['keyword_customers$customer_id'] = isset($data['id2'])
                ? $data['id2'] : '';
            $filters['keyword_customers$phone1'] = isset($data['phone'])
                ? $data['phone'] : '';
            $filters['keyword_customers$full_name'] = isset($data['fullName'])
                ? $data['fullName'] : '';
            $filters['customers$is_actived'] = isset($data['isActive'])
                ? $data['isActive'] : '';
            $list = $this->customer->getListSearch($filters);
            $page = $filters['page'];
            $view = view(
                'admin::customer-group-filter.user-define.partial.tr-user-define',
                [
                    'list' => $list,
                    'page' => $page
                ]
            )->render();
            return $view;
        } else {
            $view = view(
                'admin::customer-group-filter.user-define.partial.tr-user-define',
                []
            )->render();
            return $view;
        }
    }

    public function submitAddGroupDefine(array $data = [])
    {
        try {
            DB::beginTransaction();
            //Thêm nhóm khách hàng.
            $name = isset($data['name']) ? strip_tags($data['name']) : '';
            $dataInsertGroup = [
                'name'              => $name,
                'is_active'         => 1,
                'filter_group_type' => 'user_define',
                'created_at'        => date('Y-m-d H:s:i'),
                'updated_at'        => date('Y-m-d H:s:i'),
                'created_by'        => Auth::id(),
                'updated_by'        => Auth::id(),
            ];
            $id = $this->customerGroupFilter->add($dataInsertGroup);
            $dataDetail = [];
            $mCustomers = new CustomerTable();
            foreach ($data['arrayAccount'] as $key => $value) {
                $dataCustomer = $mCustomers->getItem($value);
                $dataDetail[] = [
                    'phone'         => $dataCustomer['phone1'],
                    'customer_id'         => $dataCustomer['customer_id'],
                    'customer_code'         => $dataCustomer['customer_code'],
                    'user_group_id' => $id,
                    'created_at'    => date('Y-m-d H:s:i'),
                    'updated_at'    => date('Y-m-d H:s:i'),
                ];
            }
            $this->customerGroupDefineDetail->add($dataDetail);

            DB::commit();
            return [
                'error'   => false,
                'message' => ''
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'error'   => $e->getMessage(),
                'message' => ''
            ];
        }
    }

    public function getItem($id)
    {
        return $this->customerGroupFilter->getItem($id);
    }

    /**
     * Lấy danh sách khách trong nhóm
     *
     * @param $id
     *
     * @return mixed
     */
    public function getCustomerByGroupDefine($id)
    {
        return $this->customerGroupDefineDetail->getDetail($id);
    }

    /**
     * Chỉnh sửa nhóm tự định nghĩa
     *
     * @param array $data
     *
     * @return array
     */
    public function updateCustomerGroupDefine(array $data = [])
    {
        try {
            DB::beginTransaction();

            //Cập nhật nhóm tự định nghĩa.
            $dataUserGroup = [
                'name'       => strip_tags($data['name']),
                'is_active'  => 1,
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => Auth::id(),
            ];
            $this->customerGroupFilter->edit($dataUserGroup, $data['id']);
            //Xóa các khách hàng trong detail để cập nhật lại.
            $this->customerGroupDefineDetail->removeByCustomerGroupId(
                $data['id']
            );

            //Thêm khách hàng vào nhóm
            $count = 0;
            $dataDetail = [];
            $mCustomers = new CustomerTable();
            foreach ($data['arrayAccount'] as $key => $value) {
                $dataCustomer = $mCustomers->getItem($value);
                $dataDetail[] = [
                    'phone'         => $dataCustomer['phone1'],
                    'customer_id'         => $dataCustomer['customer_id'],
                    'customer_code'         => $dataCustomer['customer_code'],
                    'user_group_id'         => $data['id'],
                    'created_at'    => date('Y-m-d H:s:i'),
                    'updated_at'    => date('Y-m-d H:s:i'),
                ];
            }
            $this->customerGroupDefineDetail->add($dataDetail);
            $message = [
                $count . ' ' . 'khách hàng hợp lệ',
            ];

            DB::commit();
            return [
                'error'   => false,
                'message' => $message
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'error'   => true,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Danh sách điều kiện
     *
     * @return mixed
     */
    public function getCondition(array $data = [])
    {
        $condition = $this->customerGroupCondition->getAll($data);

        return $condition;
    }

    /**
     * Danh sách nhóm khách hàng tự định nghĩa
     *
     * @return mixed
     */
    public function getCustomerGroupDefine()
    {
        return $this->customerGroupFilter->getCustomerGroupDefine();
    }

    /**
     * Danh sách tất cả dịch vụ
     *
     * @return mixed
     */
    public function getListAllService()
    {
        $listService = $this->service->getAll();
        return $listService;
    }

    /**
     * Danh sách tất cả sản phẩm
     *
     * @return mixed
     */
    public function getListAllProduct()
    {
        $listProduct = $this->productChild->getProductChildOption();
        return $listProduct;
    }
    public function getListAllServiceCard()
    {
        $listServiceCard = $this->serviceCard->getAll();
        return $listServiceCard;
    }
    public function getListAllRank()
    {
        $listMemberLevel = $this->memberLevel->getAll();
        return $listMemberLevel;
    }

    /**
     * Thêm nhóm khách hàng tự động.
     *
     * @param array $data
     *
     * @return array
     */
    public function submitAddAutoAction(array $data = [])
    {
        try {
            DB::beginTransaction();
            //            if ( ! isset($data['arrayConditionA'])
            //                || count(
            //                    $data['arrayConditionA']
            //                ) < 1
            //            ) {
            //                return [
            //                    'error'   => true,
            //                    'message' => 'Chưa chọn điều kiện'
            //                ];
            //            }
            if (!isset($data['arrayConditionA'])) {
                $data['arrayConditionA'] = [];
            }
            if (!isset($data['arrayConditionB'])) {
                $data['arrayConditionB'] = [];
            }

            //Thêm nhóm khách hàng động.
            $dataInsertGroup = [
                'name'                    => strip_tags($data['name']),
                'is_active'               => 1,
                'filter_group_type'       => 'auto',
                'created_at'              => date('Y-m-d H:i:s'),
                'updated_at'              => date('Y-m-d H:i:s'),
                'created_by'              => Auth::id(),
                'updated_by'              => Auth::id(),
                'filter_condition_rule_A' => strip_tags($data['andOrA']),
                'filter_condition_rule_B' => strip_tags($data['andOrB'])
            ];

            $id = $this->customerGroupFilter->add($dataInsertGroup);

            //Thêm chi tiết nhóm khách hàng động A.
            if (isset($data['arrayConditionA'])) {
                $this->addCustomerGroupDetail(
                    $data['arrayConditionA'],
                    $id,
                    'A',
                    $data['andOrA']
                );
            }

            //Thêm chi tiết nhóm khách hàng động B.
            if (isset($data['arrayConditionB'])) {
                $this->addCustomerGroupDetail(
                    $data['arrayConditionB'],
                    $id,
                    'B',
                    $data['andOrB']
                );
            }
            $message = '';
            DB::commit();
            return [
                'error'   => false,
                'message' => $message
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'error'   => true,
                'message' => $e->getMessage()
            ];
        }
    }

    private function addCustomerGroupDetail(
        $data,
        $id,
        $detailType = 'A',
        $rule
    ) {
        if (count($data) > 0) {
            $dataInsert = [];
            foreach ($data as $key => $value) {
                if ($value['value'] != null) {
                    $dataInsert[] = [
                        'customer_group_id'        => $id,
                        'group_type'               => $detailType,
                        'condition_rule'           => $rule,
                        'condition_id'             => intval(
                            $value['condition']
                        ),
                        'customer_group_define_id' => $value['condition'] == 1
                            ? $value['value'] : null,
                        'day_appointment'          => $value['condition'] == 2
                            ? intval($value['value']) : null,
                        'status_appointment'       => $value['condition'] == 3
                            ? $value['value'] : null,
                        'time_appointment'         => $value['condition'] == 4
                            ? $value['value'] : null,
                        'not_appointment'          => $value['condition'] == 5
                            ? ($value['value'] == 'on' ? 1 : 0) : null,
                        'use_service'              => $value['condition'] == 6
                            ? implode(',', $value['value']) : null,
                        'not_use_service'          => $value['condition'] == 7
                            ? implode(',', $value['value']) : null,
                        'use_product'              => $value['condition'] == 8
                            ? implode(',', $value['value']) : null,
                        'not_use_product'          => $value['condition'] == 9
                            ? implode(',', $value['value']) : null,
                        'not_order'          => $value['condition'] == 10
                            ? intval($value['value']) : null,
                        'inactive_app'          => $value['condition'] == 11
                            ? ($value['value'] == 'on' ? 1 : 0) : null,
                        'use_promotion'          => $value['condition'] == 12
                            ? ($value['value'] == 'on' ? 1 : 0) : null,
                        'is_rank'          => $value['condition'] == 13
                            ? implode(',', $value['value']) : null,
                        'range_point'          => $value['condition'] == 14
                            ? $value['value'] : null,
                        'top_high_revenue'          => $value['condition'] == 15
                            ? intval($value['value']) : null,
                        'top_low_revenue'          => $value['condition'] == 16
                            ? intval($value['value']) : null,
                        'use_service_card'          => $value['condition'] == 17
                            ? implode(',', $value['value']) : null,
                        'created_at'               => date('Y-m-d H:i:s'),
                        'updated_at'               => date('Y-m-d H:i:s'),
                    ];
                }
            }
            $this->customerGroupDetail->add($dataInsert);
        }
    }

    /**
     * Danh sách khách hàng của nhóm
     *
     * @param $id
     *
     * @return mixed
     */
    public function getCustomerGroupDetail($id)
    {
        return $this->customerGroupDetail->getDetail($id);
    }

    /**
     * Chỉnh sửa nhóm khách hàng tự động.
     *
     * @param array $data
     *
     * @return array
     */
    public function submitEditAutoAction(array $data = [])
    {
        try {
            DB::beginTransaction();
            $id = $data['id'];
            //            if ( ! isset($data['arrayConditionA'])
            //                || count(
            //                    $data['arrayConditionA']
            //                ) < 1
            //            ) {
            //                return [
            //                    'error'   => true,
            //                    'message' => 'Chưa chọn điều kiện'
            //                ];
            //            }
            if (!isset($data['arrayConditionA'])) {
                $data['arrayConditionA'] = [];
            }
            if (!isset($data['arrayConditionB'])) {
                $data['arrayConditionB'] = [];
            }

            //Thêm nhóm khách hàng động.
            $dataInsertGroup = [
                'name'                    => strip_tags($data['name']),
                'updated_at'              => date('Y-m-d H:i:s'),
                'updated_by'              => Auth::id(),
                'filter_condition_rule_A' => strip_tags($data['andOrA']),
                'filter_condition_rule_B' => strip_tags($data['andOrB'])
            ];

            $this->customerGroupFilter->edit($dataInsertGroup, $id);

            //Xóa hết các điều kiện của nhóm.
            $this->customerGroupDetail->removeAll($id);

            //Thêm chi tiết nhóm khách hàng động A.
            if (isset($data['arrayConditionA'])) {
                $this->addCustomerGroupDetail(
                    $data['arrayConditionA'],
                    $id,
                    'A',
                    $data['andOrA']
                );
            }
            //Thêm chi tiết nhóm khách hàng động B.
            if (isset($data['arrayConditionB'])) {
                $this->addCustomerGroupDetail(
                    $data['arrayConditionB'],
                    $id,
                    'B',
                    $data['andOrB']
                );
            }

            $message = '';
            DB::commit();
            return [
                'error'   => false,
                'message' => $message
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'error'   => true,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getCustomerInGroupAuto($id)
    {   
        
        try {
            $group = $this->customerGroupFilter->getItem($id);
            $groupDetail = $this->customerGroupDetail->getDetail($id);
            $resultAllCustomer = [];
            $resultA = [];
            $resultB = [];
            $arrayAllCustomer = $this->customer->getCustomerOption();
    
            if (count($arrayAllCustomer) > 0) {
                foreach ($arrayAllCustomer as $item) {
                    $resultAllCustomer[] = $item['customer_id'];
                }
            }
            $groupDetailA = $this->customerGroupDetail->getDetailByType($id, 'A');
            
            if (count($groupDetailA) > 0) {
        
                $arrayConditionA = $this->subGetCondition($groupDetail, 'A');
            } else {
                for ($i = 0; $i < 22; $i++) {
                    $arrayConditionA[$i] = $resultAllCustomer;
                }
            }
            if ($group['filter_condition_rule_A'] == 'or') {
                foreach ($arrayConditionA as $key => $value) {
                    foreach ($value as $k => $v) {
                        if (!in_array($v, $resultA)) {
                            $resultA[] = $v;
                        }
                    }
                }
            } else {
                foreach ($arrayConditionA as $key => $value) {
                    if (count($value) == 0) {
                        return [];
                    }
                }
                //Lấy ds KH của 9 điều kiện
                $temp1 = isset($arrayConditionA[1]) ? $arrayConditionA[1] : $resultAllCustomer;
                $temp2 = isset($arrayConditionA[2]) ? $arrayConditionA[2] : $resultAllCustomer;
                $temp3 = isset($arrayConditionA[3]) ? $arrayConditionA[3] : $resultAllCustomer;
                $temp4 = isset($arrayConditionA[4]) ? $arrayConditionA[4] : $resultAllCustomer;
                $temp5 = isset($arrayConditionA[5]) ? $arrayConditionA[5] : $resultAllCustomer;
                $temp6 = isset($arrayConditionA[6]) ? $arrayConditionA[6] : $resultAllCustomer;
                $temp7 = isset($arrayConditionA[7]) ? $arrayConditionA[7] : $resultAllCustomer;
                $temp8 = isset($arrayConditionA[8]) ? $arrayConditionA[8] : $resultAllCustomer;
                $temp9 = isset($arrayConditionA[9]) ? $arrayConditionA[9] : $resultAllCustomer;
                $temp10 = isset($arrayConditionA[10]) ? $arrayConditionA[10] : $resultAllCustomer;
                $temp11 = isset($arrayConditionA[11]) ? $arrayConditionA[11] : $resultAllCustomer;
                $temp12 = isset($arrayConditionA[12]) ? $arrayConditionA[12] : $resultAllCustomer;
                $temp13 = isset($arrayConditionA[13]) ? $arrayConditionA[13] : $resultAllCustomer;
                $temp14 = isset($arrayConditionA[14]) ? $arrayConditionA[14] : $resultAllCustomer;
                $temp15 = isset($arrayConditionA[15]) ? $arrayConditionA[15] : $resultAllCustomer;
                $temp16 = isset($arrayConditionA[16]) ? $arrayConditionA[16] : $resultAllCustomer;
                $temp17 = isset($arrayConditionA[17]) ? $arrayConditionA[17] : $resultAllCustomer;
                $temp18 = isset($arrayConditionA[18]) ? $arrayConditionA[18] : $resultAllCustomer;
                $temp19 = isset($arrayConditionA[19]) ? $arrayConditionA[19] : $resultAllCustomer;
                $temp20 = isset($arrayConditionA[20]) ? $arrayConditionA[20] : $resultAllCustomer;
                $temp21 = isset($arrayConditionA[21]) ? $arrayConditionA[21] : $resultAllCustomer;
        
                $resultA = array_intersect(
                    $temp1,
                    $temp2,
                    $temp3,
                    $temp4,
                    $temp5,
                    $temp6,
                    $temp7,
                    $temp8,
                    $temp9,
                    $temp10,
                    $temp11,
                    $temp12,
                    $temp13,
                    $temp14,
                    $temp15,
                    $temp16,
                    $temp17,
                    $temp18,
                    $temp19,
                    $temp20,
                    $temp21,
                );
            }
            $arrayConditionB = $this->subGetCondition($groupDetail, 'B');
            if ($group['filter_condition_rule_B'] == 'or') {
                foreach ($arrayConditionB as $key => $value) {
                    foreach ($value as $k => $v) {
                        if (!in_array($v, $resultB)) {
                            $resultB[] = $v;
                        }
                    }
                }
            } else {
                foreach ($arrayConditionB as $key => $value) {
                    if (count($value) == 0) {
                        $resultB = [];
                    }
                }
                $temp1 = isset($arrayConditionB[1]) ? $arrayConditionB[1] : $resultAllCustomer;
                $temp2 = isset($arrayConditionB[2]) ? $arrayConditionB[2] : $resultAllCustomer;
                $temp3 = isset($arrayConditionB[3]) ? $arrayConditionB[3] : $resultAllCustomer;
                $temp4 = isset($arrayConditionB[4]) ? $arrayConditionB[4] : $resultAllCustomer;
                $temp5 = isset($arrayConditionB[5]) ? $arrayConditionB[5] : $resultAllCustomer;
                $temp6 = isset($arrayConditionB[6]) ? $arrayConditionB[6] : $resultAllCustomer;
                $temp7 = isset($arrayConditionB[7]) ? $arrayConditionB[7] : $resultAllCustomer;
                $temp8 = isset($arrayConditionB[8]) ? $arrayConditionB[8] : $resultAllCustomer;
                $temp9 = isset($arrayConditionB[9]) ? $arrayConditionB[9] : $resultAllCustomer;
                $temp10 = isset($arrayConditionB[10]) ? $arrayConditionB[10] : $resultAllCustomer;
                $temp11 = isset($arrayConditionB[11]) ? $arrayConditionB[11] : $resultAllCustomer;
                $temp12 = isset($arrayConditionB[12]) ? $arrayConditionB[12] : $resultAllCustomer;
                $temp13 = isset($arrayConditionB[13]) ? $arrayConditionB[13] : $resultAllCustomer;
                $temp14 = isset($arrayConditionB[14]) ? $arrayConditionB[14] : $resultAllCustomer;
                $temp15 = isset($arrayConditionB[15]) ? $arrayConditionB[15] : $resultAllCustomer;
                $temp16 = isset($arrayConditionB[16]) ? $arrayConditionB[16] : $resultAllCustomer;
                $temp17 = isset($arrayConditionB[17]) ? $arrayConditionB[17] : $resultAllCustomer;
                $temp18 = isset($arrayConditionB[18]) ? $arrayConditionB[18] : $resultAllCustomer;
                $temp19 = isset($arrayConditionB[19]) ? $arrayConditionB[19] : $resultAllCustomer;
                $temp20 = isset($arrayConditionB[20]) ? $arrayConditionB[20] : $resultAllCustomer;
                $temp21 = isset($arrayConditionB[21]) ? $arrayConditionB[21] : $resultAllCustomer;
                $resultB = array_intersect(
                    $temp1,
                    $temp2,
                    $temp3,
                    $temp4,
                    $temp5,
                    $temp6,
                    $temp7,
                    $temp8,
                    $temp9,
                    $temp10,
                    $temp11,
                    $temp12,
                    $temp13,
                    $temp14,
                    $temp15,
                    $temp16,
                    $temp17,
                    $temp18,
                    $temp19,
                    $temp20,
                    $temp21
                );
            }

            //Lấy mảng A loại bỏ mảng B.
            foreach ($resultA as $key => $value) {
                if (in_array($value, $resultB)) {
                    unset($resultA[$key]);
                }
            }

            return $resultA;
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'error'   => true,
                'message' => $e->getMessage()
            ];
        }
    }

    private function subGetCondition($groupDetail, $type)
    {
        $arrayCondition = [];
        
        foreach ($groupDetail as $item) {
            if ($item['group_type'] == $type) {
                if ($item['condition_id'] == 1) {
                    $temp = [];
                    $customer = $this->customerGroupDefineDetail->getDetail(
                        $item['customer_group_define_id']
                    );
                    if (count($customer) > 0) {
                        foreach ($customer as $c) {
                            $temp[] = $c['customer_id'];
                        }
                    }
                    $arrayCondition[$item['condition_id']] = $temp;
                } elseif ($item['condition_id'] == 2) {
                    $temp2 = [];
                    $dayFrom = Carbon::now()->subDays(intval($item['day_appointment']) - 1)->format('Y-m-d');
                    $customer2
                        = $this->customerAppointment->getCustomerAppointmentDayTo(
                            $dayFrom
                        );
                    if (count($customer2) > 0) {
                        foreach ($customer2 as $c) {
                            $temp2[] = $c['customer_id'];
                        }
                    }
                    $arrayCondition[$item['condition_id']] = $temp2;
                } elseif ($item['condition_id'] == 3) {
                    $temp3 = [];
                    $customer3
                        = $this->customerAppointment->getCustomerAppointmentByStatus(
                            $item['status_appointment']
                        );
                    if (count($customer3) > 0) {
                        foreach ($customer3 as $c) {
                            $temp3[] = $c['customer_id'];
                        }
                    }
                    $arrayCondition[$item['condition_id']] = $temp3;
                } elseif ($item['condition_id'] == 4) {
                    $tempTime = [];
                    if ($item['time_appointment'] == 'morning') {
                        $tempTime = [
                            'hour_from' => '07:00:00',
                            'hour_to'   => '12:00:00'
                        ];
                    } elseif ($item['time_appointment'] == 'noon') {
                        $tempTime = [
                            'hour_from' => '12:00:01',
                            'hour_to'   => '14:00:00'
                        ];
                    } elseif ($item['time_appointment'] == 'afternoon') {
                        $tempTime = [
                            'hour_from' => '14:00:01',
                            'hour_to'   => '18:00:00'
                        ];
                    } elseif ($item['time_appointment'] == 'evening') {
                        $tempTime = [
                            'hour_from' => '18:00:01',
                            'hour_to'   => '22:00:00'
                        ];
                    }
                    $temp4 = [];
                    $customer4
                        = $this->customerAppointment->getCustomerAppointmentByTime(
                            $tempTime['hour_from'],
                            $tempTime['hour_to']
                        );

                    if (count($customer4) > 0) {
                        foreach ($customer4 as $c) {
                            $temp4[] = $c['customer_id'];
                        }
                    }
                    $arrayCondition[$item['condition_id']] = $temp4;
                } elseif ($item['condition_id'] == 5) {
                    $tempTemp5 = [];
                    $customerTemp5 = $this->customer->getCustomerNotAppointment();
                    if (count($customerTemp5) > 0) {
                        foreach ($customerTemp5 as $c) {
                            $tempTemp5[] = $c['customer_id'];
                        }
                    }
                    $customer5 = $this->customer->getCustomerNotInArrCustomerId($tempTemp5);
                    $temp5 = [];
                    if (count($customer5) > 0) {
                        foreach ($customer5 as $c) {
                            $temp5[] = $c['customer_id'];
                        }
                    }
                    $arrayCondition[$item['condition_id']] = $temp5;
                } elseif ($item['condition_id'] == 6) {
                    $arrayService = explode(',', $item['use_service']);
                    $customer6 = $this->order->getCustomerUseService(
                        $arrayService,
                        'whereIn',
                        'service'
                    );
                    $temp6 = [];
                    if (count($customer6) > 0) {
                        foreach ($customer6 as $c) {
                            $temp6[] = $c['customer_id'];
                        }
                    }
                    $arrayCondition[$item['condition_id']] = $temp6;
                } elseif ($item['condition_id'] == 7) {
                    $arrayService2 = explode(',', $item['not_use_service']);
                    $customerTemp7 = $this->order->getCustomerUseService(
                        $arrayService2,
                        'whereIn',
                        'service'
                    );
                    $customer7 = $this->customer->getCustomerNotInArrCustomerId($customerTemp7);
                    $temp7 = [];
                    if (count($customer7) > 0) {
                        foreach ($customer7 as $c) {
                            $temp7[] = $c['customer_id'];
                        }
                    }
                    $arrayCondition[$item['condition_id']] = $temp7;
                } elseif ($item['condition_id'] == 8) {
                    //Danh sách KH sử dụng SP.
                    $arrayProduct = explode(',', $item['use_product']);
                    $customer8 = $this->order->getCustomerUseService(
                        $arrayProduct,
                        'whereIn',
                        'product'
                    );
                    $temp8 = [];
                    if (count($customer8) > 0) {
                        foreach ($customer8 as $c) {
                            $temp8[] = $c['customer_id'];
                        }
                    }
                    $arrayCondition[$item['condition_id']] = $temp8;
                } elseif ($item['condition_id'] == 9) {
                    $arrayProduct2 = explode(',', $item['not_use_product']);
                    $customerTemp9 = $this->order->getCustomerUseService(
                        $arrayProduct2,
                        'whereIn',
                        'product'
                    );
                    $temp9 = [];
                    $customer9 = $this->customer->getCustomerNotInArrCustomerId($customerTemp9);
                    $temp7 = [];
                    if (count($customer9) > 0) {
                        foreach ($customer9 as $c) {
                            $temp9[] = $c['customer_id'];
                        }
                    }
                    $arrayCondition[$item['condition_id']] = $temp9;
                } elseif ($item['condition_id'] == 10) {
                    $temp10 = [];
                    $dayFrom = Carbon::now()->subDays(intval($item['not_order']) - 1)->format('Y-m-d 00:00:00');
                    $tempCustomer10
                        = $this->order->getCustomerOrderDayTo(
                            $dayFrom
                        );
                    $customer10 = $this->customer->getCustomerNotInArrCustomerId($tempCustomer10);
                    if (count($customer10) > 0) {
                        foreach ($customer10 as $c) {
                            $temp10[] = $c['customer_id'];
                        }
                    }
                    $arrayCondition[$item['condition_id']] = $temp10;
                } elseif ($item['condition_id'] == 11) {
                    $customer11 = [];
                    if ($item['inactive_app'] == 1) {
                        $customer11 = $this->customer->getCustomerNoLoginApp();
                    }
                    $temp11 = [];
                    if (count($customer11) > 0) {
                        foreach ($customer11 as $c) {
                            $temp11[] = $c['customer_id'];
                        }
                    }
                    $arrayCondition[$item['condition_id']] = $temp11;
                } elseif ($item['condition_id'] == 12) {
                    $customer12 = $this->order->getCustomerUsePromotion();
                    if ($item['use_promotion'] == 0) {
                        $customer12 = $this->customer->getCustomerNotInArrCustomerId($customer12->toArray());
                    }
                    $temp12 = [];
                    if (count($customer12) > 0) {
                        foreach ($customer12 as $c) {
                            $temp12[] = $c['customer_id'];
                        }
                    }
                    $arrayCondition[$item['condition_id']] = $temp12;
                } elseif ($item['condition_id'] == 13) {
                    $arrRank = explode(',', $item['is_rank']);
                    $customer13 = $this->customer->getCustomerInArrRank($arrRank);
                    $temp13 = [];
                    if (count($customer13) > 0) {
                        foreach ($customer13 as $c) {
                            $temp13[] = $c['customer_id'];
                        }
                    }
                    $arrayCondition[$item['condition_id']] = $temp13;
                } elseif ($item['condition_id'] == 14) {
                    $listPoint = explode(',', $item['range_point']);
                    $customer14 = $this->customer->getCustomerInRangePoint($listPoint[0], $listPoint[1]);
                    $temp14 = [];
                    if (count($customer14) > 0) {
                        foreach ($customer14 as $c) {
                            $temp14[] = $c['customer_id'];
                        }
                    }
                    $arrayCondition[$item['condition_id']] = $temp14;
                } elseif ($item['condition_id'] == 15) {
                    $customer15 = $this->receipt->getTopHighRevenueOfCustomer($item['top_high_revenue']);
                    $temp15 = [];
                    if (count($customer15) > 0) {
                        foreach ($customer15 as $c) {
                            $temp15[] = $c['customer_id'];
                        }
                    }
                    $arrayCondition[$item['condition_id']] = $temp15;
                } elseif ($item['condition_id'] == 16) {
                    $customer16 = $this->receipt->getTopLowRevenueOfCustomer($item['top_low_revenue']);
                    $temp16 = [];
                    if (count($customer16) > 0) {
                        foreach ($customer16 as $c) {
                            $temp16[] = $c['customer_id'];
                        }
                    }
                    $arrayCondition[$item['condition_id']] = $temp16;
                } elseif ($item['condition_id'] == 17) {
                    $arrayService = explode(',', $item['use_service_card']);
                    $customer17 = $this->order->getCustomerUseService(
                        $arrayService,
                        'whereIn',
                        'service_card'
                    );
                    $temp17 = [];
                    if (count($customer17) > 0) {
                        foreach ($customer17 as $c) {
                            $temp17[] = $c['customer_id'];
                        }
                    }
                    $arrayCondition[$item['condition_id']] = $temp17;
                } else if ($item['condition_id'] == 18) {
                    $objectAddress = json_decode($item['address']);
                    $customer18 = $this->customer->getCustomerByAddress($objectAddress);
                    $temp18 = [];
                    if (count($customer18) > 0) {
                        foreach ($customer18 as $c) {
                            $temp18[] = $c['customer_id'];
                        }
                    }
                    $arrayCondition[$item['condition_id']] = $temp18;
                } else if ($item['condition_id'] == 19) {
                    $customerType = explode(',', $item['type_customer']);
                    $customer19 = $this->customer->getCustomerByType($customerType);
                    $temp19 = [];
                    if (count($customer19) > 0) {
                        foreach ($customer19 as $c) {
                            $temp19[] = $c['customer_id'];
                        }
                    }
                    $arrayCondition[$item['condition_id']] = $temp19;
                } else if ($item['condition_id'] == 20) {
                    $groupCustomer = explode(',', $item['group_customer']);
                    $customer20 = $this->customer->getCustomerByGroup($groupCustomer);
                    $temp20 = [];
                    if (count($customer20) > 0) {
                        foreach ($customer20 as $c) {
                            $temp20[] = $c['customer_id'];
                        }
                    }
                    $arrayCondition[$item['condition_id']] = $temp20;

                } else if ($item['condition_id'] == 21) {
                    $sourceCustomer = explode(',', $item['source_customer']);
                    $customer21 = $this->customer->getCustomerBySource($sourceCustomer);
                    $temp21 = [];
                    if (count($customer21) > 0) {
                        foreach ($customer21 as $c) {
                            $temp21[] = $c['customer_id'];
                        }
                    }
                    $arrayCondition[$item['condition_id']] = $temp21;
                }
            }
        }
        return $arrayCondition;
    }

    public function getCustomerInGroup($id)
    {
        $result = [];
        $select = $this->customerGroupDefineDetail->getCustomerInGroup($id);
        if (count($select) > 0) {
            foreach ($select as $item) {
                $result[] = $item['customer_id'];
            }
        }
        return $result;
    }
    public function getOptionByType($type)
    {
        return $this->customerGroupFilter->getOptionByType($type);
    }

    /**
     * xoá nhóm KH tự động
     *
     * @param $id
     * @return int|string
     */
    public function deleteGroupAuto($id)
    {
        try {
            $this->customerGroupDetail->removeAll($id);
            $this->customerGroupFilter->deleteGroup($id);
            return 1;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    /**
     * Xoá nhóm KH tự định nghĩa
     *
     * @param $id
     * @return int|string
     */
    public function deleteGroupDefine($id)
    {
        try {
            $this->customerGroupDefineDetail->removeByCustomerGroupId($id);
            $this->customerGroupFilter->deleteGroup($id);
            return 1;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }
}

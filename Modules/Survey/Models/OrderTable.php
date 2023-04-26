<?php
/**
 * Created by PhpStorm.
 * User: Mr Son
 * Date: 11/27/2018
 * Time: 1:21 PM
 */

namespace Modules\Survey\Models;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use MyCore\Models\Traits\ListTableTrait;

class OrderTable extends Model
{
    use ListTableTrait;
    protected $connection = BRAND_CONNECTION;
    protected $table = "orders";
    protected $primaryKey = "order_id";
    protected $fillable = [
        'order_id',
        'order_code',
        'customer_id',
        'total',
        'discount',
        'amount',
        'tranport_charge',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'process_status',
        'order_description',
        'customer_description',
        'payment_method_id',
        'order_source_id',
        'transport_id',
        'voucher_code',
        'is_deleted',
        'branch_id',
        'refer_id',
        'discount_member',
        'is_apply',
        'customer_contact_code',
        'shipping_address',
        'receive_at_counter',
        'cashier_by',
        'cashier_date',
        'customer_contact_id',
        'receipt_info_check',
        'type_time',
        'time_address',
        'type_shipping',
        'delivery_cost_id'
    ];

    CONST IS_DELETE = 0;
    CONST IS_ACTIVE = 1;
    CONST IS_CHECK_PROMOTION = 1;
    CONST IS_VANGLAI = 1;
    CONST ARR_PAID_STATUS = ['paysuccess','pay-half'];
    CONST ARR_PAID_RECEIPT = ['part-paid','paid'];
    /**
     * @param array $data
     * @return mixed
     */
    public function add(array $data)
    {
        $add = $this->create($data);
        return $add->order_id;
    }

    public function _getList(&$filter = [])
    {
        $ds = $this
            ->select(
                'orders.order_id as order_id',
                'orders.order_code as order_code',
                'orders.total as total',
                'orders.discount as discount',
                'orders.amount as amount',
                'orders.tranport_charge as tranport_charge',
                'orders.process_status as process_status',
                'customers.full_name as full_name_cus',
                'orders.created_at as created_at',
                'staffs.full_name as full_name',
                'branches.branch_name as branch_name',
                'branches.branch_id as branch_id',
                'orders.order_description',
                'order_sources.order_source_name',
                'orders.order_source_id',
                'orders.is_apply',
                'orders.tranport_charge',
                'orders.customer_id',
                'orders.receive_at_counter'
            )
            ->join('customers', 'customers.customer_id', '=', 'orders.customer_id')
            ->leftJoin('staffs', 'staffs.staff_id', '=', 'orders.created_by')
            ->leftJoin('branches', 'branches.branch_id', '=', 'orders.branch_id')
            ->leftJoin('order_sources', 'order_sources.order_source_id', '=', 'orders.order_source_id')
            ->orderBy('orders.created_at', 'desc')
            ->where('orders.is_deleted', 0)
            ->groupBy('orders.order_id');

        if (isset($filter['search']) != "") {
            $search = $filter['search'];
            $ds->where(function ($query) use ($search) {
                $query->where('customers.full_name', 'like', '%' . $search . '%')
                    ->orWhere('order_code', 'like', '%' . $search . '%');
            });
        }
        if (isset($filter["created_at"]) && $filter["created_at"] != "") {
            $arr_filter = explode(" - ", $filter["created_at"]);
            $startTime = Carbon::createFromFormat('d/m/Y', $arr_filter[0])->format('Y-m-d');
            $endTime = Carbon::createFromFormat('d/m/Y', $arr_filter[1])->format('Y-m-d');
            $ds->whereBetween('orders.created_at', [$startTime. ' 00:00:00', $endTime. ' 23:59:59']);
        }

        if (isset($filter['receive_at_counter'])) {
            $ds = $ds->where('orders.receive_at_counter', $filter['receive_at_counter']);
            unset($filter['receive_at_counter']);
        }

        if (Auth::user()->is_admin != 1) {
            $ds->where('orders.branch_id', Auth::user()->branch_id);
        }
        return $ds;
    }

    public function getOrderByCustomer($customerId)
    {
        $ds = $this
            ->select(
                "{$this->table}.order_id",
                "{$this->table}.created_at",
                "{$this->table}.order_code",
                "{$this->table}.amount as amount",
                "{$this->table}.process_status as process_status",
                "{$this->table}.order_description",
                DB::raw("GROUP_CONCAT(order_details.object_name SEPARATOR '\n') as list_product")
            )
            ->join('order_details','order_details.order_id', '=', 'orders.order_id')
            ->where('orders.customer_id', $customerId)
            ->where('orders.is_deleted', 0)
            ->groupBy("orders.order_id")
            ->orderBy("orders.created_at", "desc");
        return $ds->get();
    }
    public function getItemDetail($id)
    {
        $ds = $this
            ->select(
                'customers.full_name as full_name',
                'customers.phone1 as phone',
                'customers.address as address',
                'customers.customer_avatar as customer_avatar',
                'customers.customer_id as customer_id',
                'customers.phone1 as phone1',
                'orders.order_code as order_code',
                'orders.total as total',
                'orders.discount as discount',
                'orders.tranport_charge as tranport_charge',
                'orders.voucher_code as voucher_code',
                'orders.amount as amount',
                'orders.process_status as process_status',
                'orders.order_id as order_id',
                'receipts.amount_paid as amount_paid',
                'customers.gender as gender',
                'orders.order_id as order_id',
                'receipts.note as note',
                'receipts.receipt_id',
                'orders.refer_id',
                'customers.member_level_id',
                'member_levels.name as member_level_name',
                'member_levels.discount as member_level_discount',
                'orders.discount_member',
                'orders.branch_id',
                'orders.order_source_id',
                'deliveries.is_actived as delivery_active',
                'deliveries.delivery_id',
                'orders.tranport_charge',
                'orders.shipping_address',
                'customer_groups.group_name as group_name_cus',
                'orders.customer_contact_code',
                'customer_contacts.postcode',
                'customer_contacts.full_address',
                'province.name as province_name',
                'district.name as district_name',
                'orders.receive_at_counter',
                'orders.created_at',
                'staffs.full_name as staff_name',
                'customers.profile_code',
                'customers.customer_code',
                "{$this->table}.receive_at_counter",
                "{$this->table}.delivery_request_date",
                "{$this->table}.order_description",
                "{$this->table}.blessing",
                "{$this->table}.customer_contact_id",
                "{$this->table}.receipt_info_check",
                "{$this->table}.type_time",
                "{$this->table}.time_address",
                "{$this->table}.type_shipping"
            )
            ->leftJoin('customers', 'customers.customer_id', '=', 'orders.customer_id')
            ->leftJoin('customer_groups', 'customer_groups.customer_group_id', '=', 'customers.customer_group_id')
            ->leftJoin('receipts', 'receipts.order_id', '=', 'orders.order_id')
            ->leftJoin('member_levels', 'member_levels.member_level_id', '=', 'customers.member_level_id')
            ->leftJoin('deliveries', 'deliveries.order_id', '=', 'orders.order_id')
            ->leftJoin('customer_contacts', 'customer_contacts.customer_contact_code', '=', 'orders.customer_contact_code')
            ->leftJoin('province', 'customer_contacts.province_id', '=', 'province.provinceid')
//            ->leftJoin('province',DB::raw("CONVERT(province.provinceid, INT)"), 'customer_contacts.province_id')
            ->leftJoin('district', 'customer_contacts.district_id', '=', 'district.districtid')
//            ->leftJoin('district', DB::raw("CONVERT(district.districtid, INT)"),'customer_contacts.district_id')
            ->leftJoin('staffs', 'staffs.staff_id', '=', 'orders.created_by')
            ->where('orders.order_id', $id);
        if (Auth::user()->is_admin != 1) {
            $ds->where('orders.branch_id', Auth::user()->branch_id);
        }
        return $ds->first();
    }

    /**
     * @param array $data
     * @param $id
     * @return mixed
     */
    public function edit(array $data, $id)
    {
        return $this->where('order_id', $id)->update($data);
    }

    /**
     * @param $id
     */
    public function remove($id)
    {
        $this->where('order_id', $id)->update(['is_deleted' => 1, 'process_status' => 'payfail']);
    }

    public function detailDayCustomer($id)
    {
        $ds = $this->select('created_at', DB::raw('count(created_at) as number'))
            ->where('customer_id', $id)
            ->groupBy(DB::raw('Date(created_at)'))
            ->get();
        return $ds;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function detailCustomer($id)
    {
        $ds = $this
            ->leftJoin('branches', 'branches.branch_id', '=', 'orders.branch_id')
            ->leftJoin('receipts', 'receipts.order_id', '=', 'orders.order_id')
            ->select('orders.order_code',
                'orders.amount',
                'orders.process_status',
                'branches.branch_name as branch_name',
                'orders.created_at as created_at',
                'orders.order_id',
                'receipts.note',
                'orders.order_description',
                "{$this->table}.order_source_id"
            )
            ->where('orders.customer_id', $id)
            ->groupBy("orders.order_id")
            ->orderBy('orders.created_at', 'DESC')->get();
        return $ds;
    }

    public function getIndexReportRevenue()
    {
        $select = $this->where('is_deleted', 0)->get();
        return $select;
    }

    public function getValueByYear($year, $startTime = null, $endTime = null)
    {
        $select = null;
        if ($year != null) {
            $yearTime = substr($startTime, 0, -6);
            if ($yearTime != false) {
                $select = $this->where('is_deleted', 0)->whereYear('created_at', $yearTime);
            } else {
                $select = $this->where('is_deleted', 0)->whereYear('created_at', $year);
            }
            if ($startTime != null) {
                $select->whereBetween('created_at', [$startTime . " 00:00:00", $endTime . " 23:59:59"]);
            }
        } else {
            if ($startTime != null) {
                $select = $this->whereBetween('created_at', [$startTime . " 00:00:00", $endTime . " 23:59:59"]);
            }
        }
        if (Auth::user()->is_admin != 1) {
            $select->where('branch_id', Auth::user()->branch_id);
        }
        return $select->get();
    }

    public function getValueByDate($date, $field = null, $valueField = null, $field2 = null, $valueField2 = null)
    {
        $select = null;
        if ($field == null || $valueField == null) {
            $select = $this->select(DB::raw('sum(amount) as total'))
                ->whereBetween('created_at', [$date . " 00:00:00", $date . " 23:59:59"])
                ->where('process_status', 'paysuccess')->where('is_deleted', 0);
        } else if ($field != null && $valueField != null && $field2 == null && $valueField2 == null) {
            $select = $this->select(DB::raw('sum(amount) as total'))
                ->whereBetween('created_at', [$date . " 00:00:00", $date . " 23:59:59"])
                ->where($field, $valueField)
                ->where('process_status', 'paysuccess')
                ->where('is_deleted', 0);
        } else if ($field != null && $valueField != null && $field2 != null && $valueField2 != null) {
            $select = $this->select(DB::raw('sum(amount) as total'))
                ->whereBetween('created_at', [$date . " 00:00:00", $date . " 23:59:59"])
                ->where([$field => $valueField], [$field2 => $valueField2])
                ->where('process_status', 'paysuccess')
                ->where('is_deleted', 0);
        }
        return $select->get();
    }

    //Lấy dữ liệu với tham số truyền vào(thời gian, cột)
    public function getValueByParameter($date, $filer, $valueFilter)
    {
        $select = $this->select(DB::raw('sum(amount) as total'))
            ->whereBetween('created_at', [$date . " 00:00:00", $date . " 23:59:59"])
            ->where($filer, $valueFilter)->where('process_status', 'paysuccess')->where('is_deleted', 0)
            ->get();
        return $select;
    }

    //Lấy giá trị từ ngày - đến ngày.
    public function getValueByDay($startTime, $endTime)
    {
        $select = $this->where('is_deleted', 0)->whereBetween('created_at', [$startTime . " 00:00:00", $endTime . " 23:59:59"]);
        return $select->get();
    }

    //Lấy dữ liệu với tham số truyền vào(thời gian, cột) 2
    public function getValueByParameter2($startTime, $endTime, $filer, $valueFilter)
    {
        $select = null;
        if ($filer == null && $valueFilter == null) {
            $select = $this->whereBetween('created_at', [$startTime . " 00:00:00", $endTime . " 23:59:59"])
                ->where('is_deleted', 0);
        } else {
            $select = $this->whereBetween('created_at', [$startTime . " 00:00:00", $endTime . " 23:59:59"])
                ->where($filer, $valueFilter)->where('is_deleted', 0);
        }
        return $select->get();
    }

    //Lấy giá trị theo năm, cột và giá trị cột truyền vào
    public function fetchValueByParameter($year, $startTime, $endTime, $field, $fieldValue)
    {
        $select = null;
        if ($startTime == null && $endTime == null) {
            $select = $this->whereYear('created_at', $year)
                ->where($field, $fieldValue)->where('is_deleted', 0);
        } else {
            $select = $this->whereBetween('created_at', [$startTime . " 00:00:00", $endTime . " 23:59:59"])
                ->where($field, $fieldValue)->where('is_deleted', 0);
        }
        return $select->get();
    }

    //Lấy giá trị theo năm, cột và giá trị 2 cột truyền vào
    public function fetchValueByParameter2($year, $startTime, $endTime, $field, $fieldValue, $field2, $fieldValue2)
    {
        $select = null;
        if ($year == null) {
            $select = $this->whereBetween('created_at', [$startTime . " 00:00:00", $endTime . " 23:59:59"])
                ->where($field, $fieldValue)
                ->where($field2, $fieldValue2)
                ->where('is_deleted', 0);
        } else {
            $select = $this->whereYear('created_at', $year)
                ->where($field, $fieldValue)
                ->where($field2, $fieldValue2)
                ->where('is_deleted', 0);
        }
        return $select->get();
    }

    //Lấy các giá trị theo created_at, branch_id và customer_id
    public function getValueByDate2($date, $branch, $customer)
    {
        $select = $this->select('amount')
            ->whereBetween('created_at', [$date . " 00:00:00", $date . " 23:59:59"])
            ->where('branch_id', $branch)
            ->where('customer_id', $customer)
            ->where('process_status', 'paysuccess')
            ->where('is_deleted', 0);
        return $select->get();
    }

    //Lấy các giá trị theo created_at, branch_id và created_by
    public function getValueByDate3($date, $branch, $staff)
    {
        $select = null;
        if ($branch != null) {
            $select = $this->select('amount')
                ->whereBetween('created_at', [$date . " 00:00:00", $date . " 23:59:59"])
                ->where('branch_id', $branch)
                ->where('created_by', $staff)
                ->where('process_status', 'paysuccess')
                ->where('is_deleted', 0);

        } else {
            $select = $this->select('amount')
                ->whereBetween('created_at', [$date . " 00:00:00", $date . " 23:59:59"])
                ->where('created_by', $staff)
                ->where('process_status', 'paysuccess')
                ->where('is_deleted', 0);
        }
        return $select->get();
    }

    //Lấy danh sách khách hàng cho tăng trưởng khách hàng.
    public function getDataReportGrowthByCustomer($year, $month, $operator, $customerOdd, $field, $valueField)
    {
        $select = null;
        $select = $this->leftJoin('customers', 'customers.customer_id', '=', 'orders.customer_id')
            ->select(
                'customers.customer_id as customer_id',
                'orders.created_at as order_created_at',
                'orders.branch_id as order_branch_id'
            );

        if ($field == null || $valueField == null) {
            if ($customerOdd == null) {
                if ($operator == null) {
                    //Khách hàng mới.
                    $select->whereYear('customers.created_at', $year)
                        ->whereMonth('customers.created_at', $month)
                        ->whereYear('orders.created_at', $year)
                        ->whereMonth('orders.created_at', $month)
                        ->where('customers.customer_id', '<>', 1);

                } else {
                    //Khách hàng cũ
                    $select->whereMonth('customers.created_at', $operator, $month)
                        ->whereYear('orders.created_at', $year)
                        ->whereMonth('orders.created_at', $month)
                        ->where('customers.customer_id', '<>', 1);
                }
                $select->groupBy('orders.customer_id');
            } else {
                //Khách vãng lai.
                $select->whereYear('orders.created_at', $year)
                    ->whereMonth('orders.created_at', $month)
                    ->where('customers.customer_id', '=', 1);
            }
        } else {
            if ($customerOdd == null) {
                if ($operator == null) {
                    //Khách hàng mới.
                    $select->whereYear('customers.created_at', $year)
                        ->whereMonth('customers.created_at', $month)
                        ->whereYear('orders.created_at', $year)
                        ->whereMonth('orders.created_at', $month)
                        ->where($field, $valueField)
                        ->where('customers.customer_id', '<>', 1);

                } else {
                    //Khách hàng cũ
                    $select->whereMonth('customers.created_at', $operator, $month)
                        ->whereYear('orders.created_at', $year)
                        ->whereMonth('orders.created_at', $month)
                        ->where($field, $valueField)
                        ->where('customers.customer_id', '<>', 1);
                }
                $select->groupBy('orders.customer_id');
            } else {
                //Khách vãng lai.
                $select->whereYear('orders.created_at', $year)
                    ->whereMonth('orders.created_at', $month)
                    ->where($field, $valueField)
                    ->where('customers.customer_id', '=', 1);
            }
        }
        $select->where('customers.is_deleted', 0)
            ->where('orders.is_deleted', 0)
            ->where('orders.process_status', 'paysuccess');

        return $select->get();

    }

    //Lấy danh sách khách hàng cho tăng trưởng khách hàng theo năm cho từng chi nhánh.
    public function getDataReportGrowthCustomerByYear($year, $operator, $customerOdd, $branch)
    {
        $select = null;
        $select = $this->leftJoin('customers', 'customers.customer_id', '=', 'orders.customer_id')
            ->select(
                'customers.customer_id as customer_id',
                'orders.created_at as created_at');

        if ($customerOdd == null) {
            if ($operator == null) {
                //Khách hàng mới.
                $select->whereYear('customers.created_at', $year)
                    ->whereYear('orders.created_at', $year)
                    ->where('orders.branch_id', $branch)
                    ->where('customers.customer_id', '<>', 1);

            } else {
                //Khách hàng cũ
                $select->where('orders.branch_id', $branch)
                    ->whereYear('customers.created_at', '<>', $year)
                    ->whereYear('orders.created_at', $year)
                    ->where('customers.customer_id', '<>', 1);
            }
            $select->groupBy('orders.customer_id');
        } else {
            //Khách vãng lai.
            $select->whereYear('orders.created_at', $year)
                ->where('orders.branch_id', $branch)
                ->where('customers.customer_id', '=', 1);
        }
        $select->where('customers.is_deleted', 0)
            ->where('orders.is_deleted', 0)
            ->where('orders.process_status', 'paysuccess');
        return $select->get();
    }

    //Thống kê tăng trưởng khách hàng(theo nhóm khách hàng).
    public function getValueReportGrowthByCustomerCustomerGroup($year, $branch)
    {
        $select = null;
        if ($branch == null) {
            $select = $this->leftJoin('customers', 'customers.customer_id', '=', 'orders.customer_id')
                ->leftJoin('customer_groups', 'customer_groups.customer_group_id', '=', 'customers.customer_group_id')
                ->select(
                    'customer_groups.group_name as group_name',
                    DB::raw("COUNT(orders.customer_id) as totalCustomer")
                );
        } else {
            $select = $this->leftJoin('customers', 'customers.customer_id', '=', 'orders.customer_id')
                ->leftJoin('customer_groups', 'customer_groups.customer_group_id', '=', 'customers.customer_group_id')
                ->select(
                    'customer_groups.group_name as group_name',
                    DB::raw("COUNT(orders.customer_id) as totalCustomer")
                )
                ->where('orders.branch_id', $branch);
        }
        $select->where('orders.is_deleted', 0)
            ->where('orders.process_status', 'paysuccess')
            ->whereYear('orders.created_at', $year)
            ->where('customers.is_deleted', 0)
            ->where('orders.customer_id', '<>', 1)
            ->groupBy('customer_groups.customer_group_id');
        return $select->get();
    }

    //Thống kê tăng trưởng khách hàng(theo nguồn khách hàng).
    public function getValueReportGrowthByCustomerCustomerSource($year, $branch)
    {
        $select = $this->leftJoin('customers', 'customers.customer_id', '=', 'orders.customer_id')
            ->leftJoin('customer_sources', 'customer_sources.customer_source_id', '=', 'customers.customer_source_id')
            ->select(
                'customer_sources.customer_source_name as customer_source_name',
                DB::raw("COUNT(orders.customer_id) as totalCustomer")
            );
        if ($branch != null) {
            $select->where('orders.branch_id', $branch);
        }
        $select->where('orders.is_deleted', 0)
            ->where('orders.process_status', 'paysuccess')
            ->whereYear('orders.created_at', $year)
            ->where('customer_sources.is_deleted', 0)
            ->where('customers.is_deleted', 0)
            ->where('orders.customer_id', '<>', 1)
            ->groupBy('customer_sources.customer_source_id');
        return $select->get();
    }

    //Thống kê tăng trưởng khách hàng(theo giới tính).
    public function getValueReportGrowthByCustomerCustomerGender($year, $branch)
    {
        $select = $this->leftJoin('customers', 'customers.customer_id', '=', 'orders.customer_id')
            ->select(
                'customers.gender as gender',
                DB::raw("COUNT(orders.customer_id) as totalCustomer")
            );
        if ($branch != null) {
            $select->where('orders.branch_id', $branch);
        }
        $select->where('orders.is_deleted', 0)
            ->where('orders.process_status', 'paysuccess')
            ->whereYear('orders.created_at', $year)
            ->where('customers.is_deleted', 0)
            ->where('orders.customer_id', '<>', 1)
            ->groupBy('customers.gender');
        return $select->get();
    }

    //Lấy danh sách khách hàng cho tăng trưởng khách hàng(từ ngày đến ngày và/hoặc chi nhánh).
    public function getDataReportGrowthByCustomerDataBranch($startTime, $endTime, $operator, $customerOdd, $branch)
    {
        $select = null;
        $select = $this->leftJoin('customers', 'customers.customer_id', '=', 'orders.customer_id')
            ->select(
                'customers.customer_id as customer_id',
                'orders.created_at as order_created_at',
                'orders.branch_id as order_branch_id'
            );
        if (Auth::user()->is_admin != 1) {
            $branch = Auth::user()->branch_id;
        }
//        dd(Auth::user()->branch_id, $startTime, $endTime, $operator, $customerOdd, $branch);
        if ($branch == null) {
            if ($customerOdd == null) {
                if ($operator == null) {
                    //Khách hàng mới.
                    $select->whereBetween('customers.created_at', [$startTime . " 00:00:00", $endTime . " 23:59:59"])
                        ->whereBetween('orders.created_at', [$startTime . " 00:00:00", $endTime . " 23:59:59"])
                        ->where('customers.customer_id', '<>', 1);

                } else {
                    //Khách hàng cũ
                    $select->whereNotBetween('customers.created_at', [$startTime . " 00:00:00", $endTime . " 23:59:59"])
                        ->whereBetween('orders.created_at', [$startTime . " 00:00:00", $endTime . " 23:59:59"])
                        ->where('customers.customer_id', '<>', 1);
                }
                $select->groupBy('orders.customer_id');
            } else {
                //Khách vãng lai.
                $select->whereBetween('orders.created_at', [$startTime . " 00:00:00", $endTime . " 23:59:59"])
                    ->where('customers.customer_id', '=', 1);
            }
        } else {
            if ($customerOdd == null) {
                if ($operator == null) {
                    //Khách hàng mới.
                    $select->whereBetween('customers.created_at', [$startTime . " 00:00:00", $endTime . " 23:59:59"])
                        ->whereBetween('orders.created_at', [$startTime . " 00:00:00", $endTime . " 23:59:59"])
                        ->where('customers.customer_id', '<>', 1)
                        ->where('orders.branch_id', $branch);

                } else {
                    //Khách hàng cũ
                    $select->whereNotBetween('customers.created_at', [$startTime . " 00:00:00", $endTime . " 23:59:59"])
                        ->whereBetween('orders.created_at', [$startTime . " 00:00:00", $endTime . " 23:59:59"])
                        ->where('customers.customer_id', '<>', 1)
                        ->where('orders.branch_id', $branch);
                }
                $select->groupBy('orders.customer_id');
            } else {
                //Khách vãng lai.
                $select->whereBetween('orders.created_at', [$startTime . " 00:00:00", $endTime . " 23:59:59"])
                    ->where('customers.customer_id', '=', 1)
                    ->where('orders.branch_id', $branch);
            }
        }
        $select->where('customers.is_deleted', 0)
            ->where('orders.is_deleted', 0)
            ->where('orders.process_status', 'paysuccess');

        return $select->get();
    }

    //Thống kê tăng trưởng khách hàng(theo nhóm khách hàng) theo từ ngày đến ngày và/hoặc chi nhánh.
    public function getValueReportGrowthByCustomerCustomerGroupTimeBranch($startTime, $endTime, $branch)
    {
        $select = null;
        if (Auth::user()->is_admin != 1) {
            $branch = Auth::user()->branch_id;
        }
        if ($branch == null) {
            $select = $this->leftJoin('customers', 'customers.customer_id', '=', 'orders.customer_id')
                ->leftJoin('customer_groups', 'customer_groups.customer_group_id', '=', 'customers.customer_group_id')
                ->select(
                    'customer_groups.group_name as group_name',
                    DB::raw("COUNT(orders.customer_id) as totalCustomer")
                );
        } else {
            $select = $this->leftJoin('customers', 'customers.customer_id', '=', 'orders.customer_id')
                ->leftJoin('customer_groups', 'customer_groups.customer_group_id', '=', 'customers.customer_group_id')
                ->select(
                    'customer_groups.group_name as group_name',
                    DB::raw("COUNT(orders.customer_id) as totalCustomer")
                )
                ->where('orders.branch_id', $branch);
        }
        $select->where('orders.is_deleted', 0)
            ->where('orders.process_status', 'paysuccess')
            ->whereBetween('orders.created_at', [$startTime . " 00:00:00", $endTime . " 23:59:59"])
            ->where('customers.is_deleted', 0)
            ->where('orders.customer_id', '<>', 1)
            ->groupBy('customer_groups.customer_group_id');
        return $select->get();
    }

    //Thống kê tăng trưởng khách hàng(theo giới tính) theo từ ngày đến ngày và/hoặc chi nhánh.
    public function getValueReportGrowthByCustomerCustomerGenderTimeBranch($startTime, $endTime, $branch)
    {
        if (Auth::user()->is_admin != 1) {
            $branch = Auth::user()->branch_id;
        }
        $select = $this->leftJoin('customers', 'customers.customer_id', '=', 'orders.customer_id')
            ->select(
                'customers.gender as gender',
                DB::raw("COUNT(orders.customer_id) as totalCustomer")
            );
        if ($branch != null) {
            $select->where('orders.branch_id', $branch);
        }
        $select->where('orders.is_deleted', 0)
            ->where('orders.process_status', 'paysuccess')
            ->whereBetween('orders.created_at', [$startTime . " 00:00:00", $endTime . " 23:59:59"])
            ->where('customers.is_deleted', 0)
            ->where('orders.customer_id', '<>', 1)
            ->groupBy('customers.gender');
        return $select->get();
    }

    //Thống kê tăng trưởng khách hàng(theo nguồn khách hàng) theo từ ngày tới ngày và/hoặc chi nhánh.
    public function getValueReportGrowthByCustomerCustomerSourceTimeBranch($startTime, $endTime, $branch)
    {
        if (Auth::user()->is_admin != 1) {
            $branch = Auth::user()->branch_id;
        }
        $select = $this->leftJoin('customers', 'customers.customer_id', '=', 'orders.customer_id')
            ->leftJoin('customer_sources', 'customer_sources.customer_source_id', '=', 'customers.customer_source_id')
            ->select(
                'customer_sources.customer_source_name as customer_source_name',
                DB::raw("COUNT(orders.customer_id) as totalCustomer")
            );
        if ($branch != null) {
            $select->where('orders.branch_id', $branch);
        }
        $select->where('orders.is_deleted', 0)
            ->where('orders.process_status', 'paysuccess')
            ->whereBetween('orders.created_at', [$startTime . " 00:00:00", $endTime . " 23:59:59"])
            ->where('customer_sources.is_deleted', 0)
            ->where('customers.is_deleted', 0)
            ->where('orders.customer_id', '<>', 1)
            ->groupBy('customer_sources.customer_source_id');
        return $select->get();
    }

    //Lấy dữ liệu theo năm/từ ngày đến ngày và chi nhánh
    public function getValueByYearAndBranch($year, $branch, $startTime, $endTime)
    {
        $select = null;
        if ($year != null) {
            $select = $this->whereYear('created_at', $year);
        } else {
            if ($startTime != null) {
                $select = $this->whereBetween('created_at', [$startTime . " 00:00:00", $endTime . " 23:59:59"]);
            }
        }
        if ($branch != null) {
            $select->where('branch_id', $branch);
        }
        return $select->get(['customer_id', 'branch_id', 'process_status', 'order_source_id', 'created_at']);
    }

    //Lấy danh sách khách hàng cho tăng trưởng khách hàng theo năm cho từng chi nhánh.
    public function getDataReportGrowthCustomerByTime($startTime, $endTime, $operator, $customerOdd, $branch)
    {
        $select = null;
        $select = $this->leftJoin('customers', 'customers.customer_id', '=', 'orders.customer_id')
            ->select(
                'customers.customer_id as customer_id',
                'orders.created_at as created_at');

        if ($customerOdd == null) {
            if ($operator == null) {
                //Khách hàng mới.
                $select->whereBetween('customers.created_at', [$startTime . " 00:00:00", $endTime . " 23:59:59"])
                    ->whereBetween('orders.created_at', [$startTime . " 00:00:00", $endTime . " 23:59:59"])
                    ->where('orders.branch_id', $branch)
                    ->where('customers.customer_id', '<>', 1);

            } else {
                //Khách hàng cũ
                $select->where('orders.branch_id', $branch)
                    ->whereNotBetween('customers.created_at', [$startTime . " 00:00:00", $endTime . " 23:59:59"])
                    ->whereBetween('orders.created_at', [$startTime . " 00:00:00", $endTime . " 23:59:59"])
                    ->where('customers.customer_id', '<>', 1);
            }
            $select->groupBy('orders.customer_id');
        } else {
            //Khách vãng lai.
            $select->whereBetween('orders.created_at', [$startTime . " 00:00:00", $endTime . " 23:59:59"])
                ->where('orders.branch_id', $branch)
                ->where('customers.customer_id', '=', 1);
        }
        $select->where('customers.is_deleted', 0)
            ->where('orders.is_deleted', 0)
            ->where('orders.process_status', 'paysuccess');
        return $select->get();
    }

    //search dashboard
    public function searchDashboard($keyword)
    {
        $time = Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now()->subDays(30))->format('Y-m-d');
        $select = $this->leftJoin('customers', 'customers.customer_id', '=', 'orders.customer_id')
            ->leftJoin('staffs', 'staffs.staff_id', '=', 'orders.created_by')
            ->select(
                'orders.order_id as order_id',
                'orders.order_code as order_code',
                'customers.full_name as full_name',
                'customers.phone1 as phone1',
                'staffs.full_name as staff_name',
                'orders.total as total',
                'orders.process_status as process_status',
                'orders.created_at as created_at',
                'orders.order_id as order_id',
                'customers.customer_avatar as customer_avatar'
            )
            ->where(function ($query) use ($keyword) {
                $query->where('customers.full_name', 'like', '%' . $keyword . '%')
                    ->orWhere('customers.phone1', 'like', '%' . $keyword . '%')
                    ->orWhere('customers.email', 'like', '%' . $keyword . '%')
                    ->orWhere('orders.order_code', 'like', '%' . $keyword . '%');
            })
            ->where('orders.created_at', '>', $time . ' 00:00:00');
        if (Auth::user()->is_admin != 1) {
            $select->where('orders.branch_id', Auth::user()->branch_id);
        };
        return $select->get();
    }

    public function getAllByCondition($startTime, $endTime, $branch)
    {
        if (Auth::user()->is_admin != 1) {
            $branch = Auth::user()->branch_id;
        }
        $select = $this->where('orders.process_status', 'paysuccess');
        if ($branch != null) {
            $select->where('orders.branch_id', $branch);
        }
        $select->where('orders.is_deleted', 0)
            ->whereBetween('orders.created_at', [$startTime . " 00:00:00", $endTime . " 23:59:59"]);
        return $select->get();
    }

    public function getCustomerDetail($id)
    {
        $ds = $this->leftJoin('customers', 'customers.customer_id', '=', 'orders.customer_id')
            ->select('customers.full_name as full_name',
                'customers.phone1 as phone',
                'orders.process_status as process_status',
                'orders.order_id as order_id',
                'customers.gender as gender',
                'customers.customer_id as customer_id',
                'orders.order_code as order_code'
            )
            ->where('orders.order_id', $id);
        return $ds->first();
    }

    //Lấy dữ liệu với tham số truyền vào(thời gian, cột) 2. Lấy tiền đã thanh toán
    public function getValueByParameter3(
        $startTime,
        $endTime,
        $filer,
        $valueFilter
    ) {

        $select = $this
            ->leftJoin('receipts', 'receipts.order_id', '=', 'orders.order_id')
            ->select(
                'orders.order_id',
                'orders.order_code',
                'orders.customer_id',
                'orders.branch_id',
                'orders.total',
                'orders.discount',
                'receipts.amount_paid as amount',
                'orders.tranport_charge',
                'orders.created_by',
                'orders.updated_by',
                'orders.created_at',
                'orders.updated_at',
                'orders.process_status',
                'orders.order_description',
                'orders.payment_method_id',
                'orders.order_source_id',
                'orders.transport_id',
                'orders.voucher_code',
                'orders.is_deleted',
                'receipts.amount as total_amount',
                'receipts.status as receipts_status'
            )
            ->where('orders.is_deleted', 0);
        if ($filer == null && $valueFilter == null) {
            $select->whereBetween('orders.created_at',
                [$startTime . " 00:00:00", $endTime . " 23:59:59"]
            );
        } else {
            $select->whereBetween('orders.created_at',
                [$startTime . " 00:00:00", $endTime . " 23:59:59"])
                ->where($filer, $valueFilter)->where('orders.is_deleted', 0);
        }

        return $select->get();
    }

    public function getValueByParameter4(
        $startTime,
        $endTime,
        $filer,
        $valueFilter,
        $customerGroup
    ) {

        $select = $this
//            ->leftJoin('receipts', 'receipts.order_id', '=', 'orders.order_id')
            ->join('customers','customers.customer_id','orders.customer_id')
            ->select(
                'orders.order_id',
                'orders.branch_id',
                'orders.total',
                'orders.discount',
                'orders.process_status',
                'orders.payment_method_id',
                DB::raw('SUM(amount) as total_order'),
                DB::raw('COUNT(*) as count_order')
            )
            ->where('orders.is_deleted', 0)
            ->where('orders.process_status','new');
        if ($filer == null && $valueFilter == null) {
            $select->whereBetween('orders.created_at',
                [$startTime . " 00:00:00", $endTime . " 23:59:59"]
            );
        } else {
            $select->whereBetween('orders.created_at',
                [$startTime . " 00:00:00", $endTime . " 23:59:59"])
                ->where($filer, $valueFilter)->where('orders.is_deleted', 0);
        }

        if ($customerGroup != null) {
            $select->where('customers.customer_group_id',$customerGroup);
        }

        return $select->first();
//        return $select->get();
    }

    public function getValueByYear2($year, $startTime, $endTime)
    {
        $select = $this->leftJoin('receipts', 'receipts.order_id', '=',
            'orders.order_id')
            ->select(
                'orders.order_id',
                'orders.order_code',
                'orders.customer_id',
                'orders.branch_id',
                'orders.total',
                'orders.discount',
                'receipts.amount_paid as amount',
                'orders.tranport_charge',
                'orders.created_by',
                'orders.updated_by',
                'orders.created_at',
                'orders.updated_at',
                'orders.process_status',
                'orders.order_description',
                'orders.payment_method_id',
                'orders.order_source_id',
                'orders.transport_id',
                'orders.voucher_code',
                'orders.is_deleted',
                'receipts.amount as total_amount',
                'receipts.status as  receipts_status'
            )
            ->where('orders.is_deleted', 0);
        if ($year != null) {
            $yearTime = substr($startTime, 0, -6);
            if ($yearTime != false) {
                $select->where('orders.is_deleted', 0)
                    ->whereYear('orders.created_at', $yearTime);
            } else {
                $select->where('orders.is_deleted', 0)
                    ->whereYear('orders.created_at', $year);
            }
            if ($startTime != null) {
                $select->whereBetween('orders.created_at',
                    [$startTime . " 00:00:00", $endTime . " 23:59:59"]);
            }
        } else {
            if ($startTime != null) {
                $select->whereBetween('orders.created_at',
                    [$startTime . " 00:00:00", $endTime . " 23:59:59"]);
            }
        }
        if (Auth::user()->is_admin != 1) {
            $select->where('orders.branch_id', Auth::user()->branch_id);
        }
        return $select->get();
    }

    //Lấy giá trị theo năm, cột và giá trị cột truyền vào. Lấy tiền thanh toán trong receipt.
    public function fetchValueByParameter3($year, $startTime, $endTime, $field, $fieldValue)
    {
        $select = $this->leftJoin('receipts', 'receipts.order_id', '=',
            'orders.order_id')
            ->select(
                'orders.order_id',
                'orders.order_code',
                'orders.customer_id',
                'orders.branch_id',
                'orders.total',
                'orders.discount',
                'receipts.amount_paid as amount',
                'orders.tranport_charge',
                'orders.created_by',
                'orders.updated_by',
                'orders.created_at',
                'orders.updated_at',
                'orders.process_status',
                'orders.order_description',
                'orders.payment_method_id',
                'orders.order_source_id',
                'orders.transport_id',
                'orders.voucher_code',
                'orders.is_deleted',
                'receipts.amount as total_amount',
                'receipts.status as  receipts_status'
            )
            ->where('orders.is_deleted', 0);
        if ($startTime == null && $endTime == null) {
            $select ->whereYear('orders.created_at', $year)
                ->where($field, $fieldValue)->where('orders.is_deleted', 0);
        } else {
            $select ->whereBetween('orders.created_at', [$startTime . " 00:00:00", $endTime . " 23:59:59"])
                ->where($field, $fieldValue)->where('orders.is_deleted', 0);
        }
        return $select->get();
    }

    /**
     * Danh sách KH từng sử dụng/ chưa sử dụng dịch vụ.
     * @param $arrService
     * @param $where
     * @return mixed
     */
    public function getCustomerUseService($arrService, $where, $type)
    {
        $select = $this->select('orders.customer_id')
            ->leftJoin('customers','customers.customer_id', '=', 'orders.customer_id')
            ->join('order_details','order_details.order_id', '=', 'orders.order_id')
            ->where('customers.is_deleted', 0)
            ->where('customers.is_actived', 1)
            ->where('customers.customer_id','!=', 1)
            ->where('orders.process_status', 'paysuccess')
            ->where('order_details.object_type', $type);
        if ($where == 'whereIn') {
            $select->whereIn('order_details.object_id', $arrService);
        } elseif ($where == 'whereNotIn') {
            $select->whereNotIn('order_details.object_id', $arrService);
        }
        return $select->distinct('orders.customer_id')->get();
    }

    /**
     * Lấy thông tin đơn hàng
     *
     * @param $orderId
     * @return mixed
     */
    public function getOrderById($orderId)
    {
        $select = $this->select(
            'order_id',
            'order_code',
            'customer_id',
            'total',
            'discount',
            'amount',
            'tranport_charge',
            'process_status',
            'order_description',
            'customer_description',
            'payment_method_id',
            'order_source_id',
            'transport_id',
            'voucher_code',
            'is_deleted',
            'branch_id',
            'refer_id',
            'discount_member',
            'customer_contact_code',
            'shipping_address',
            'receive_at_counter'
        )
            ->where('order_id', $orderId)
            ->where('is_deleted', 0);
        return $select->first();
    }

    public function getAll(&$filter = [])
    {
        $ds = $this
            ->select(
                'orders.order_id as order_id',
                'orders.order_code as order_code',
                'orders.total as total',
                'orders.discount as discount',
                'orders.amount as amount',
                'orders.tranport_charge as tranport_charge',
                'orders.process_status as process_status',
                'customers.full_name as full_name_cus',
                'orders.created_at as created_at',
                'staffs.full_name as full_name',
                'branches.branch_name as branch_name',
                'branches.branch_id as branch_id',
                'orders.order_description',
                'order_sources.order_source_name',
                'orders.order_source_id',
                'orders.is_apply',
                'orders.tranport_charge'
            )
            ->join('customers', 'customers.customer_id', '=', 'orders.customer_id')
            ->leftJoin('staffs', 'staffs.staff_id', '=', 'orders.created_by')
            ->leftJoin('branches', 'branches.branch_id', '=', 'orders.branch_id')
            ->leftJoin('order_sources', 'order_sources.order_source_id', '=', 'orders.order_source_id')
            ->orderBy('orders.created_at', 'desc')
            ->where('orders.is_deleted', 0)
            ->groupBy('orders.order_id');

        if (isset($filter['search']) != "") {
            $search = $filter['search'];
            $ds->where(function ($query) use ($search) {
                $query->where('customers.full_name', 'like', '%' . $search . '%')
                    ->orWhere('order_code', 'like', '%' . $search . '%');
            });
        }
        if (isset($filter["created_at"]) && $filter["created_at"] != "") {
            $arr_filter = explode(" - ", $filter["created_at"]);
            $startTime = Carbon::createFromFormat('d/m/Y', $arr_filter[0])->format('Y-m-d');
            $endTime = Carbon::createFromFormat('d/m/Y', $arr_filter[1])->format('Y-m-d');
            $ds->whereBetween('orders.created_at', [$startTime. ' 00:00:00', $endTime. ' 23:59:59']);
        }
        if (Auth::user()->is_admin != 1) {
            $ds->where('orders.branch_id', Auth::user()->branch_id);
        }
        // filter list
        foreach ($filter as $key => $val)
        {
            if (trim($val) == '') {
                continue;
            }

            $ds->where(str_replace('$', '.', $key), $val);
        }
        return $ds->paginate(1000000000, $columns = ['*'], $pageName = 'page', 1);
    }
    /**
     * Danh sách KH từ ngày .... now
     * @param $day
     * @return mixed
     */
    public function getCustomerOrderDayTo($day)
    {
        $select = $this->select(
            'orders.customer_id'
        )
            ->join('customers', 'customers.customer_id', '=', 'orders.customer_id')
            ->where('customers.customer_id','!=', self::IS_VANGLAI)
            ->where('customers.is_deleted', self::IS_DELETE)
            ->where('customers.is_actived', self::IS_ACTIVE)
            ->where('orders.created_at', '>', $day)
            ->groupBy('orders.customer_id');
        return $select->get()->toArray();
    }
    public function getCustomerUsePromotion()
    {
        $select = $this->select(
            'orders.customer_id'
        )
            ->leftJoin("promotion_logs","promotion_logs.order_id","{$this->table}.order_id")
            ->join('customers', 'customers.customer_id', '=', 'orders.customer_id')
            ->where('customers.customer_id','!=', self::IS_VANGLAI)
            ->where('customers.is_deleted', self::IS_DELETE)
            ->where('customers.is_actived', self::IS_ACTIVE);
        return $select->get();
    }

    /**
     * số lần sử dụng voucher bởi khách hàng
     *
     * @param $customerId
     * @param $voucherCode
     * @return mixed
     */
    public function getOrderOfCustomerUsingVoucherCode($customerId, $voucherCode)
    {
        $select = $this->select(
            'orders.order_id',
            'orders.customer_id'
        )
            ->where('orders.customer_id', $customerId)
            ->where('orders.voucher_code', $voucherCode)
            ->whereNotIn('orders.process_status', ['ordercancle']);
        return $select->get();
    }

    /**
     * Lấy chi tiết đơn hàng
     */
    public function getDetailOrder($orderId){
        return $this
            ->where('order_id',$orderId)
            ->first();
    }

    /**
     * Lấy đơn hàng để export data cho Sie
     *
     * @param $beforeDate
     * @return mixed
     */
    public function getOrderExportSie($beforeDate)
    {
        return $this
            ->select(
                "orders.order_id as order_id",
                'orders.order_code as order_code',
                'orders.total as total',
                'orders.discount as discount',
                'orders.amount as amount',
                'orders.tranport_charge',
                'orders.process_status as process_status',
                'customers.full_name as customer_name',
                "customers.phone1 as customer_phone",
                'orders.created_at',
                'staffs.full_name as staff_name',
                'branches.branch_name as branch_name',
                'branches.branch_id as branch_id',
                'orders.order_description',
                'order_sources.order_source_name',
                'orders.order_source_id',
                'orders.is_apply',
                'orders.tranport_charge'
            )
            ->join('customers', 'customers.customer_id', '=', 'orders.customer_id')
            ->leftJoin('staffs', 'staffs.staff_id', '=', 'orders.created_by')
            ->leftJoin('branches', 'branches.branch_id', '=', 'orders.branch_id')
            ->leftJoin('order_sources', 'order_sources.order_source_id', '=', 'orders.order_source_id')
            ->where('orders.is_deleted', 0)
            ->where("{$this->table}.created_at", "<", $beforeDate)
            ->groupBy('orders.order_id')
            ->get();
    }
}
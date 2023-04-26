<?php

/**
 * Created by PhpStorm.
 * User: Mr Son
 * Date: 11/2/2018
 * Time: 4:09 PM
 */

namespace Modules\Survey\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use MyCore\Models\Traits\ListTableTrait;

class CustomerTable extends Model
{
    use ListTableTrait;
    protected $connection = BRAND_CONNECTION;
    protected $table = 'customers';
    protected $primaryKey = 'customer_id';
    protected $fillable = [
        'customer_id',
        'branch_id',
        'customer_group_id',
        'type_customer',
        'customer_type',
        'tax_code',
        'representative',
        'hotline',
        'full_name',
        'birthday',
        'gender',
        'phone1',
        'phone2',
        'email',
        'facebook',
        'address',
        'customer_source_id',
        'customer_refer_id',
        'customer_avatar',
        'note',
        'date_last_visit',
        'is_actived',
        'is_deleted',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'zalo',
        'account_money',
        'customer_code',
        'province_id',
        'district_id',
        'ward_id',
        'custom_1',
        'custom_2',
        'custom_3',
        'custom_4',
        'custom_5',
        'custom_6',
        'custom_7',
        'custom_8',
        'custom_9',
        'custom_10',
        'total_commission',
        'profile_code'
    ];

    const IS_DELETE = 0;
    const IS_ACTIVE = 1;
    const IS_VANGLAI = 1;

    /**
     * Danh sách khách hàng
     *
     * @return mixed
     */
    protected function _getList($filter = [])
    {
        $ds = $this
            ->select(
                'customers.customer_id as customer_id',
                'customers.branch_id as branch_id',
                'customers.full_name as full_name',
                'customers.birthday as birthday',
                'customers.gender as gender',
                'customers.email as email',
                'customers.phone1 as phone1',
                'customers.customer_code as customer_code',
                'customer_groups.group_name as group_name',
                'customers.customer_avatar as customer_avatar',
                'customers.created_at as created_at',
                'customers.updated_at as updated_at',
                'customers.customer_group_id as customer_group_id',
                'branches.branch_name as branch_name',
                'customers.is_actived',
                "staffs.full_name as staff_name",
                "{$this->table}.profile_code"
            )
            ->leftJoin('branches', 'branches.branch_id', '=', 'customers.branch_id')
            ->leftJoin('customer_groups', 'customer_groups.customer_group_id', '=', 'customers.customer_group_id')
            ->leftJoin("staffs", "staffs.staff_id", "=", "{$this->table}.created_by")
            ->leftJoin("customer_branch as cb", "cb.customer_id", "=", "{$this->table}.customer_id")
            ->where('customers.is_deleted', 0)
            ->where('customers.customer_id', '<>', 1)
            ->groupBy("{$this->table}.customer_id")
            ->orderBy('customers.customer_id', 'desc');

        if (isset($filter["created_at"]) != "") {
            $arr_filter = explode(" - ", $filter["created_at"]);
            $startTime = Carbon::createFromFormat('d/m/Y', $arr_filter[0])->format('Y-m-d');
            $endTime = Carbon::createFromFormat('d/m/Y', $arr_filter[1])->format('Y-m-d');
            $ds->whereBetween('customers.created_at', [$startTime . ' 00:00:00', $endTime . ' 23:59:59']);
        }


        if (isset($filter["birthday"]) != "") {
            $arr_filter = explode(" - ", $filter["birthday"]);
            $from = Carbon::createFromFormat('m/d/Y', $arr_filter[0])->format('Y-m-d');
            $ds->whereDate('customers.birthday', $from);
        }
        if (isset($filter['search']) != "") {
            $search = $filter['search'];
            $ds->where(function ($query) use ($search) {
                $query->where('customers.full_name', 'like', '%' . $search . '%')
                    ->orWhere('customers.customer_code', 'like', '%' . $search . '%')
                    ->orWhere('customers.phone1', 'like', '%' . $search . '%')
                    ->orWhere('customers.profile_code', 'like', '%' . $search . '%')
                    ->where('customers.is_deleted', 0);
            });
        }

        //Phân quyền xem khách hàng theo chi nhánh
        if (Auth::user()->is_admin != 1 && in_array('permission-customer-branch', session('routeList'))) {
            $ds->where("cb.branch_id", Auth::user()->branch_id);
        }

        return $ds;
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function add(array $data)
    {
        $add = $this->create($data);
        return $add->customer_id;
    }

    /**
     * Tìm kiếm khách hàng
     *
     * @param $data
     * @return mixed
     */
    public function getCustomerSearch($data)
    {
        $select = $this
            ->select(
                "{$this->table}.customer_id",
                "{$this->table}.customer_group_id",
                "{$this->table}.full_name",
                "{$this->table}.phone1",
                "{$this->table}.customer_avatar",
                "{$this->table}.account_money",
                "{$this->table}.address",
                "{$this->table}.postcode",
                "{$this->table}.province_id as province_id",
                "{$this->table}.district_id as district_id",
                "{$this->table}.ward_id as ward_id",
                "group.group_name as group_name",
                "province.name as province_name",
                "district.name as district_name"
            )
            ->leftJoin('customer_groups as group', 'group.customer_group_id', '=', 'customers.customer_group_id')
            ->leftJoin('province', 'province.provinceid', '=', 'customers.province_id')
            ->leftJoin('district', 'district.districtid', '=', 'customers.district_id')
            ->leftJoin("customer_branch as cb", "cb.customer_id", "=", "{$this->table}.customer_id")
            //            ->where('full_name', 'like', '%' . $data . '%')
            //            ->orWhere('phone1', 'like', '%' . $data . '%')

            ->where(function ($query) use ($data) {
                $query->where('full_name', 'like', '%' . $data . '%')
                    ->orWhere('phone1', 'like', '%' . $data . '%');
            })
            ->where('customers.is_deleted', 0)
            ->where('customers.is_actived', 1)
            ->groupBy("{$this->table}.customer_id");

        //Phân quyền xem khách hàng theo chi nhánh
        if (Auth::user()->is_admin != 1 && in_array('permission-customer-branch', session('routeList'))) {
            $select->where("cb.branch_id", Auth::user()->branch_id);
        }


        return $select->paginate(6);
    }

    /**
     * Lấy thông tin khách hàng
     *
     * @param $id
     * @return mixed
     */
    public function getItem($id)
    {
        $get = $this
            ->leftJoin('customer_groups as group', 'group.customer_group_id', '=', 'customers.customer_group_id')
            ->leftJoin('customer_sources as source', 'source.customer_source_id', '=', 'customers.customer_source_id')
            //            ->leftJoin('province', 'province.provinceid', '=', 'customers.province_id')
            ->leftJoin('province', 'province.provinceid', '=', 'customers.province_id')
            //            ->leftJoin('district', 'district.districtid', '=', 'customers.district_id')
            ->leftJoin('district', 'district.districtid', '=', 'customers.district_id')
            ->leftJoin('ward', 'ward.ward_id', '=', 'customers.ward_id')
            ->leftJoin('member_levels', 'member_levels.member_level_id', '=', 'customers.member_level_id')
            ->select(
                'customers.customer_group_id as customer_group_id',
                'group.group_name as group_name',
                'customers.full_name as full_name',
                'customers.customer_type as customer_type',
                'customers.tax_code as tax_code',
                'customers.representative as representative',
                'customers.hotline as hotline',
                'customers.customer_code as customer_code',
                'customers.gender as gender',
                'customers.phone1 as phone1',
                'province.name as province_name',
                'province.type as province_type',
                'district.name as district_name',
                'district.type as district_type',
                'ward.name as ward_name',
                'ward.type as ward_type',
                'customers.address as address',
                'customers.email as email',
                'customers.customer_source_id as customer_source_id',
                'customers.birthday as birthday',
                'source.customer_source_name',
                'customers.customer_refer_id',
                'customers.facebook as facebook',
                'customers.zalo as zalo',
                'customers.note as note',
                'customers.customer_id as customer_id',
                'customers.is_actived as is_actived',
                'customers.phone2 as phone2',
                'customers.customer_avatar as customer_avatar',
                'customers.created_at as created_at',
                'customers.account_money as account_money',
                'customers.province_id as province_id',
                'customers.district_id as district_id',
                'customers.ward_id as ward_id',
                'customers.point as point',
                'customers.member_level_id as member_level_id',
                'member_levels.name as member_level_name',
                'customers.point as point',
                'member_levels.discount as member_level_discount',
                "{$this->table}.postcode",
                "{$this->table}.custom_1",
                "{$this->table}.custom_2",
                "{$this->table}.custom_3",
                "{$this->table}.custom_4",
                "{$this->table}.custom_5",
                "{$this->table}.custom_6",
                "{$this->table}.custom_7",
                "{$this->table}.custom_8",
                "{$this->table}.custom_9",
                "{$this->table}.custom_10",
                "{$this->table}.total_commission",
                "{$this->table}.profile_code"
            )
            ->where('customers.customer_id', $id);
        //        if (Auth::user()->is_admin != 1) {
        //            $get->where('customers.branch_id', Auth::user()->branch_id);
        //        }
        return $get->first();
    }

    public function getItemLog($id)
    {
        $get = $this
            ->select(
                'customers.customer_group_id',
                'customers.full_name',
                'customers.gender',
                'customers.phone1',
                'customers.phone2',
                'customers.email',
                'customers.facebook',
                'customers.province_id',
                'customers.district_id',
                'customers.address',
                'customers.customer_source_id',
                'customers.customer_refer_id',
                'customers.note',
                'customers.is_actived',
                "{$this->table}.postcode",
                "{$this->table}.custom_1",
                "{$this->table}.custom_2",
                "{$this->table}.custom_3",
                "{$this->table}.custom_4",
                "{$this->table}.custom_5",
                "{$this->table}.custom_6",
                "{$this->table}.custom_7",
                "{$this->table}.custom_8",
                "{$this->table}.custom_9",
                "{$this->table}.custom_10",
                DB::raw("DATE_FORMAT(customers.birthday, '%Y-%m-%d') as birthday"),
                'customers.customer_avatar'
            )
            ->where('customers.customer_id', $id);
        return $get->first();
    }
    public function getItemFull($id)
    {
        $get = $this
            ->where('customers.customer_id', $id);
        return $get->first();
    }


    /**
     * @param $id
     */
    public function getItemRefer($id)
    {
        $get = $this
            ->Join('customers as cs', 'cs.customer_refer_id', '=', 'customers.customer_id')
            ->select(
                'customers.full_name as full_name_refer',
                'customers.customer_id as customer_id'
            )
            ->where('cs.customer_id', $id)
            ->first();
        return $get;
    }

    /**
     * @param array $data
     * @param $id
     * @return mixed
     */
    public function edit(array $data, $id)
    {
        return $this->where('customer_id', $id)->update($data);
    }

    /**
     * @param $id
     */
    public function remove($id)
    {
        $this->where('customer_id', $id)->update(['is_deleted' => 1]);
    }

    /**
     * Lấy option khách hàng
     *
     * @return mixed
     */
    public function getCustomerOption()
    {
        $ds = $this
            ->select(
                "{$this->table}.full_name",
                "{$this->table}.customer_code",
                "{$this->table}.customer_id",
                "{$this->table}.phone1"
            )
            ->leftJoin("customer_branch as cb", "cb.customer_id", "=", "{$this->table}.customer_id")
            ->where("{$this->table}.is_actived", 1)
            ->where("{$this->table}.is_deleted", 0)
            ->where("{$this->table}.customer_id", '!=', 1)
            ->groupBy("{$this->table}.customer_id");

        return $ds->get()->toArray();
    }

    public function getCustomerOptionOptimize($listCustomerId)
    {
        return $this->select('full_name', 'customer_code', 'customer_id', 'phone1')
            ->whereIn('customer_id', $listCustomerId)
            ->where('is_actived', 1)
            ->where('is_deleted', 0)
            ->where('customer_id', '!=', 1)->get()->toArray();
    }

    /**
     * @param $phone
     * @param $id
     * @return mixed
     * Kiểm tra số điện thoại đã tồn tại chưa
     */
    public function testPhone($phone, $id)
    {
        return $this->where(function ($query) use ($phone) {
            $query->where('phone1', '=', $phone)
                ->orWhere('phone2', '=', $phone);
        })->where('customer_id', '<>', $id)
            ->where('is_deleted', 0)->first();
    }

    /**
     * Tìm kiếm khách hàng theo sđt
     *
     * @param $phone
     * @return mixed
     */
    public function searchPhone($phone)
    {
        $select = $this
            ->select(
                "{$this->table}.customer_id",
                "{$this->table}.full_name",
                "{$this->table}.phone1"
            )
            ->leftJoin("customer_branch as cb", "cb.customer_id", "=", "{$this->table}.customer_id")
            ->where("{$this->table}.phone1", 'like', '%' . $phone . '%')
            ->where("{$this->table}.is_actived", 1)
            ->where("{$this->table}.is_deleted", 0)
            ->groupBy("{$this->table}.customer_id");

        //Phân quyền xem khách hàng theo chi nhánh
        if (Auth::user()->is_admin != 1 && in_array('permission-customer-branch', session('routeList'))) {
            $select->where("cb.branch_id", Auth::user()->branch_id);
        }

        return $select->get();
    }

    /**
     * Lấy thông tin khách hàng sđt
     *
     * @param $phone
     * @return mixed
     */
    public function getCusPhone($phone)
    {
        $select = $this
            ->select(
                "{$this->table}.customer_id",
                "{$this->table}.customer_code",
                "{$this->table}.full_name",
                "{$this->table}.phone1",
                DB::raw("IFNULL(customer_groups.group_name,'') as group_name"),
                "{$this->table}.customer_group_id"
            )
            ->leftJoin("customer_groups", "customer_groups.customer_group_id", "{$this->table}.customer_group_id")
            ->leftJoin("customer_branch as cb", "cb.customer_id", "=", "{$this->table}.customer_id")
            ->where("{$this->table}.phone1", $phone)
            ->where("{$this->table}.is_actived", 1)
            ->where("{$this->table}.is_deleted", 0)
            ->where("{$this->table}.customer_id", "<>", 1)
            ->whereNotNull("{$this->table}.phone1")
            ->groupBy("{$this->table}.customer_id");

        //Phân quyền xem khách hàng theo chi nhánh
        if (Auth::user()->is_admin != 1 && in_array('permission-customer-branch', session('routeList'))) {
            $select->where("cb.branch_id", Auth::user()->branch_id);
        }

        return $select->first();
    }

    public function getCusPhone2($phone)
    {
        $select = $this->select('customer_id', 'full_name', 'phone1')
            ->where('phone1', $phone)
            ->where('is_deleted', 0);
        return $select->first();
    }

    /**
     * @return mixed
     * Tổng số khách hàng từ năm hiện tại trở về trước
     */
    public function totalCustomer($yearNow)
    {

        $ds = $this->select(DB::raw('count(customer_id) as number'))
            ->where(DB::raw('YEAR(created_at)'), '<=', $yearNow)
            ->where('is_deleted', 0)->get();
        return $ds;
    }

    /**
     * @param $dayNow
     * @return mixed
     * Tổng số khách hàng đã tạo trong năm hiện tại
     */
    public function totalCustomerNow($yearNow)
    {

        $ds = $this->select(DB::raw('count(customer_id) as number'))
            //            ->whereRaw("DATE_FORMAT(created_at,'%Y-%m-%d') = DATE_FORMAT('$yearNow','%Y-%m-%d')")
            ->whereRaw("YEAR(created_at)=$yearNow")
            ->where('is_deleted', 0)
            ->get();
        return $ds;
    }

    /**
     * @param $year
     * @param $branch
     * @return mixed
     * Tổng số khách hàng trong năm hiện tại trở về trước và chi nhánh
     */
    public function filterCustomerYearBranch($year, $branch)
    {

        $ds = $this->select(DB::raw('count(customer_id) as number'))
            ->where(DB::raw('YEAR(created_at)'), '<=', $year)->where('is_deleted', 0);
        if (!is_null($branch)) {
            $ds->where('branch_id', $branch);
        }
        return $ds->get();
    }

    /**
     * @param $year
     * @param $branch
     * @return mixed
     * Tổng số khách hàng trong năm hiện tại và chi nhánh
     */
    public function filterNowCustomerBranch($year, $branch)
    {
        $ds = $this->select(DB::raw('count(customer_id) as number'))
            ->where(DB::raw('YEAR(created_at)'), '=', $year)->where('is_deleted', 0);
        if (!is_null($branch)) {
            $ds->where('branch_id', $branch);
        }
        return $ds->get();
    }

    /**
     * @param $time
     * @param $branch
     * @return mixed
     * Tổng số KH từ thời gian endTime trở về trước và chi nhánh
     */
    public function filterTimeToTime($time, $branch)
    {

        $arr_filter = explode(" - ", $time);
        $startTime = Carbon::createFromFormat('d/m/Y', $arr_filter[0])->format('Y-m-d');
        $endTime = Carbon::createFromFormat('d/m/Y', $arr_filter[1])->format('Y-m-d');
        $ds = $this->select(DB::raw('count(customer_id) as number'))
            ->where(DB::raw('DATE(created_at)'), '<=', $endTime)->where('is_deleted', 0);
        if (!is_null($branch)) {
            $ds->where('branch_id', $branch);
        }
        return $ds->get();
    }

    /**
     * @param $time
     * @param $branch
     * @return mixed
     * Tổng số KH từ khoản thời gian start time và end time
     */
    public function filterTimeNow($time, $branch)
    {
        $arr_filter = explode(" - ", $time);
        $startTime = Carbon::createFromFormat('d/m/Y', $arr_filter[0])->format('Y-m-d');
        $endTime = Carbon::createFromFormat('d/m/Y', $arr_filter[1])->format('Y-m-d');
        $ds = $this->select(DB::raw('count(customer_id) as number'))
            ->whereBetween(DB::raw('DATE(created_at)'), [$startTime, $endTime])
            ->where('is_deleted', 0);
        if (!is_null($branch)) {
            $ds->where('branch_id', $branch);
        }
        return $ds->get();
    }

    public function searchCustomerEmail($data, $birthday, $gender, $branch)
    {
        $select = $this->leftJoin('branches', 'branches.branch_id', '=', 'customers.branch_id')
            ->select(
                'customers.customer_id',
                'customers.full_name',
                'customers.phone1',
                'customers.birthday',
                'customers.gender',
                'branches.branch_name',
                'customers.email'
            )
            ->where('customers.is_actived', 1)
            ->where('customers.is_deleted', 0)
            ->where('customers.customer_id', '<>', 1);
        if ($data != null) {
            $select->where(function ($query) use ($data, $birthday, $gender, $branch) {
                $query->where('customers.full_name', 'like', '%' . $data . '%')
                    ->orWhere('customers.phone1', 'like', '%' . $data . '%')
                    ->orWhere('customers.email', 'like', '%' . $data . '%');
            });
        }
        if ($birthday != null) {
            $select->where('customers.birthday', $birthday);
        }
        if ($gender != null) {
            $select->where('customers.gender', $gender);
        }
        if ($branch != null) {
            $select->where('customers.branch_id', $branch);
        }

        if ($data == null && $birthday == null && $gender == null && $branch == null) {
            $select->limit(500);
        }

        return $select->get();
    }

    /**
     * Lấy ds khách hàng gửi email
     *
     * @param $data
     * @param $birthday
     * @param $gender
     * @param $branch
     * @param $arrPhone
     * @param $arrEmail
     * @return mixed
     */
    public function searchCustomerPhoneEmail($data, $birthday, $gender, $branch, $arrPhone, $arrEmail)
    {
        $select = $this
            ->select(
                "customers.customer_id",
                "customers.full_name",
                "customers.phone1",
                "customers.birthday",
                "customers.gender",
                "branches.branch_name",
                "customers.email"
            )
            ->leftJoin('branches', 'branches.branch_id', '=', 'customers.branch_id')
            ->leftJoin("customer_branch as cb", "cb.customer_id", "=", "{$this->table}.customer_id")
            ->where("customers.is_actived", 1)
            ->where("customers.is_deleted", 0)
            ->where("customers.customer_id", '<>', 1)
            ->groupBy("{$this->table}.customer_id");

        if ($data != null) {
            $select->where(function ($query) use ($data, $birthday, $gender, $branch) {
                $query->where('customers.full_name', 'like', '%' . $data . '%')
                    ->orWhere('customers.phone1', 'like', '%' . $data . '%')
                    ->orWhere('customers.email', 'like', '%' . $data . '%');
            });
        }
        if ($birthday != null) {
            $select->where('customers.birthday', $birthday);
        }
        if ($gender != null) {
            $select->where('customers.gender', $gender);
        }
        if ($branch != null) {
            $select->where('customers.branch_id', $branch);
        }

        if ($data == null && $birthday == null && $gender == null && $branch == null) {
            $select->limit(500);
        }

        if (count($arrPhone) != 0) {
            $select->whereNotNull('customers.phone1')
                ->whereNotIn('customers.phone1', $arrPhone);
        }

        if (count($arrEmail) != 0) {
            $select->whereNotNull('customers.email')
                ->whereNotIn('customers.email', $arrEmail);
        }

        //Phân quyền xem khách hàng theo chi nhánh
        if (Auth::user()->is_admin != 1 && in_array('permission-customer-branch', session('routeList'))) {
            $select->where("cb.branch_id", Auth::user()->branch_id);
        }

        return $select->get();
    }

    public function getBirthdays()
    {
        $select = $this
            ->whereMonth('birthday', '=', date('m'))
            ->whereDay('birthday', '=', date('d'))
            ->where('is_deleted', 0)
            ->where('is_actived', 1)
            ->whereNotNull('phone1')
            ->get();
        return $select;
    }

    //search dashboard
    public function searchDashboard($keyword)
    {
        $select = $this->select(
            'customer_id',
            'full_name',
            'phone1',
            'customers.email as email',
            'branches.branch_name as branch_name',
            'customers.updated_at as updated_at',
            'customer_avatar',
            'customer_id',
            'group_name',
            'customer_avatar'
        )
            ->leftJoin('branches', 'branches.branch_id', '=', 'customers.branch_id')
            ->leftJoin('customer_groups', 'customer_groups.customer_group_id', '=', 'customers.customer_group_id')
            ->where(function ($query) use ($keyword) {
                $query->where('customers.full_name', 'like', '%' . $keyword . '%')
                    ->orWhere('customers.phone1', 'like', '%' . $keyword . '%')
                    ->orWhere('customers.email', 'like', '%' . $keyword . '%');
            })
            ->where('customers.is_deleted', 0)
            ->where('customers.is_actived', 1)
            ->where('customers.customer_id', '<>', 1)
            ->get();
        return $select;
    }

    /**
     * Báo cáo công nợ theo khách hàng
     *
     * @param $id_branch
     * @param $time
     * @param $top
     * @return mixed
     */
    public function reportCustomerDebt($id_branch, $time, $top)
    {
        $ds = $this
            ->leftJoin('customer_debt', 'customer_debt.customer_id', '=', 'customers.customer_id')
            ->leftJoin('branches', 'branches.branch_id', '=', 'customers.branch_id')
            ->select(
                'customers.full_name',
                'customer_debt.debt_type',
                'customer_debt.status',
                'customer_debt.amount',
                'customer_debt.amount_paid'
            );
        if (isset($id_branch)) {
            $ds->where('branches.branch_id', $id_branch);
        }
        if (isset($time) && $time != "") {
            $arr_filter = explode(" - ", $time);
            $startTime = Carbon::createFromFormat('d/m/Y', $arr_filter[0])->format('Y-m-d');
            $endTime = Carbon::createFromFormat('d/m/Y', $arr_filter[1])->format('Y-m-d');
            $ds->whereBetween('customer_debt.created_at', [$startTime . ' 00:00:00', $endTime . ' 23:59:59']);
        }
        return $ds->get();
    }

    protected function getListCore(&$filters = [])
    {
        $oSelect = $this
            ->select(
                'customers.customer_id as customer_id',
                'customers.branch_id as branch_id',
                'customers.full_name as full_name',
                'customers.birthday as birthday',
                'customers.gender as gender',
                'customers.email as email',
                'customers.phone1 as phone1',
                'customers.customer_code as customer_code',
                'customer_groups.group_name as group_name',
                'customers.customer_avatar as customer_avatar',
                'customers.created_at as created_at',
                'customers.updated_at as updated_at',
                'customers.customer_group_id as customer_group_id',
                'branches.branch_name as branch_name',
                'customers.is_actived'
            )
            ->leftJoin('branches', 'branches.branch_id', '=', 'customers.branch_id')
            ->leftJoin('customer_groups', 'customer_groups.customer_group_id', '=', 'customers.customer_group_id')
            ->leftJoin("customer_branch as cb", "cb.customer_id", "=", "{$this->table}.customer_id")
            ->where('customers.is_deleted', 0)
            ->where('customers.customer_id', '<>', 1)
            ->orderBy('customers.customer_id', 'desc')
            ->groupBy("{$this->table}.customer_id");

        if (isset($filters['arrayUser'])) {
            $oSelect->whereIn("{$this->table}.customer_id", $filters['arrayUser']);
            unset($filters['arrayUser']);
        }
        //        if (isset($filters['arrayUser'])) {
        //            $oSelect->whereIn('phone1', $filters['arrayUser']);
        //            unset($filters['arrayUser']);
        //        }

        //Phân quyền xem khách hàng theo chi nhánh
        if (Auth::user()->is_admin != 1 && in_array('permission-customer-branch', session('routeList'))) {
            $oSelect->where("cb.branch_id", Auth::user()->branch_id);
        }

        return $oSelect;
    }

    public function getCustomerInGroupAuto($arrayCondition)
    {
        $select = $this
            ->select(
                'customers.customer_id'
            )
            ->leftJoin('customer_appointments', 'customer_appointments.customer_id', '=', 'customers.customer_id')
            ->leftJoin("customer_branch as cb", "cb.customer_id", "=", "{$this->table}.customer_id")
            ->where('customers.is_deleted', 0)
            ->groupBy("{$this->table}.customer_id");

        foreach ($arrayCondition as $key => $value) {
            if ($key == 1) {
                $select->leftJoin(
                    'customer_group_define_detail',
                    'customer_group_define_detail.phone',
                    '=',
                    'customers.phone1'
                )->orWhere('customer_group_define_detail.id', $value);
            } elseif ($key == 2) {
                $select->orWhere('customer_appointments.date', '>=', $value);
            } elseif ($key == 3) {
                $select->orWhere('customer_appointments.status', '=', $value);
            } elseif ($key == 4) {
                $select->orWhereBetween('customer_appointments.time', [$value['hour_from'], $value['hour_to']]);
            }
        }

        //Phân quyền xem khách hàng theo chi nhánh
        if (Auth::user()->is_admin != 1 && in_array('permission-customer-branch', session('routeList'))) {
            $select->where("cb.branch_id", Auth::user()->branch_id);
        }

        return $select->get();
    }

    public function getCustomerNotAppointment()
    {
        $select = $this
            ->select('customers.customer_id')
            ->rightJoin(
                'customer_appointments',
                'customer_appointments.customer_id',
                'customers.customer_id'
            )
            ->leftJoin("customer_branch as cb", "cb.customer_id", "=", "{$this->table}.customer_id")
            ->where('customers.is_deleted', 0)
            ->where('customers.is_actived', 1)
            ->where('customers.customer_id', '!=', 1)
            ->groupBy("{$this->table}.customer_id");

        //Phân quyền xem khách hàng theo chi nhánh
        if (Auth::user()->is_admin != 1 && in_array('permission-customer-branch', session('routeList'))) {
            $select->where("cb.branch_id", Auth::user()->branch_id);
        }

        return $select->get();
    }

    public function getCustomerUseService($arrService, $where)
    {
        $select = $this
            ->select(
                'customers.customer_id',
                'orders.order_id',
                'order_details.object_type',
                'order_details.object_id',
                'orders.process_status'
            )
            ->leftJoin('orders', 'orders.customer_id', '=', 'customers.customer_id')
            ->leftJoin('order_details', 'order_details.order_id', '=', 'orders.order_id')
            ->leftJoin("customer_branch as cb", "cb.customer_id", "=", "{$this->table}.customer_id")
            ->where('customers.is_deleted', 0)
            ->where('customers.is_actived', 1)
            ->groupBy("{$this->table}.customer_id");

        //Phân quyền xem khách hàng theo chi nhánh
        if (Auth::user()->is_admin != 1 && in_array('permission-customer-branch', session('routeList'))) {
            $select->where("cb.branch_id", Auth::user()->branch_id);
        }

        return $select->get();
    }

    public function getCustomerNotUseService($arrService, $where, $type)
    {
        $select = $this
            ->select(
                'customers.customer_id'
            )
            ->leftJoin('orders', 'orders.customer_id', '=', 'customers.customer_id')
            ->leftJoin('order_details', 'order_details.order_id', '=', 'orders.order_id')
            ->leftJoin("customer_branch as cb", "cb.customer_id", "=", "{$this->table}.customer_id")
            ->where('customers.is_deleted', 0)
            ->where('customers.is_actived', 1)
            ->where('customers.customer_id', '!=', 1)
            ->where('order_details.object_type', $type)
            ->groupBy("{$this->table}.customer_id");

        if ($where == 'whereIn') {
            $select->whereIn('order_details.object_id', $arrService);
        } elseif ($where == 'whereNotIn') {
            $select->whereNotIn('order_details.object_id', $arrService);
        }

        //Phân quyền xem khách hàng theo chi nhánh
        if (Auth::user()->is_admin != 1 && in_array('permission-customer-branch', session('routeList'))) {
            $select->where("cb.branch_id", Auth::user()->branch_id);
        }

        return $select->distinct('orders.customer_id')->get();
    }

    /**
     * Lấy List full khách hàng
     *
     * @param array $filter
     * @return mixed
     */
    public function getAllCustomer($filter = [])
    {
        $ds = $this
            ->select(
                "{$this->table}.customer_id",
                "{$this->table}.full_name",
                "{$this->table}.phone1",
                "{$this->table}.gender",
                "{$this->table}.member_level_id",
                DB::raw("CONCAT(province.type, ' ', province.name, ', ', district.type, ' ', district.name, ', ', {$this->table}.address) 
                    as address"),
                "{$this->table}.birthday",
                "{$this->table}.email",
                "member_levels.name as member_level_name",
                "{$this->table}.note",
                "{$this->table}.customer_code",
                "{$this->table}.profile_code"
            )
            ->leftJoin("province", "province.provinceid", "=", "{$this->table}.province_id")
            ->leftJoin("district", "district.districtid", "=", "{$this->table}.district_id")
            ->leftJoin("member_levels", "member_levels.member_level_id", "=", "{$this->table}.member_level_id")
            ->leftJoin("customer_branch as cb", "cb.customer_id", "=", "{$this->table}.customer_id")
            ->where("{$this->table}.customer_id", '!=', 1)
            ->where("{$this->table}.is_deleted", 0)
            ->groupBy("{$this->table}.customer_id");

        //Phân quyền xem khách hàng theo chi nhánh
        if (Auth::check() == true && Auth::user()->is_admin != 1 && in_array('permission-customer-branch', session('routeList'))) {
            $ds->where("cb.branch_id", Auth::user()->branch_id);
        }

        //Filter theo ngày tạo
        if (isset($filter["created_at"]) &&  $filter["created_at"] != null) {
            $arr_filter = explode(" - ", $filter["created_at"]);
            $startTime = Carbon::createFromFormat('d/m/Y', $arr_filter[0])->format('Y-m-d');
            $endTime = Carbon::createFromFormat('d/m/Y', $arr_filter[1])->format('Y-m-d');
            $ds->whereBetween('customers.created_at', [$startTime . ' 00:00:00', $endTime . ' 23:59:59']);
        }

        if (isset($filter['search']) && $filter['search'] != null) {
            $search = $filter['search'];
            $ds->where(function ($query) use ($search) {
                $query->where('customers.full_name', 'like', '%' . $search . '%')
                    ->orWhere('customers.customer_code', 'like', '%' . $search . '%')
                    ->orWhere('customers.phone1', 'like', '%' . $search . '%')
                    ->orWhere('customers.profile_code', 'like', '%' . $search . '%')
                    ->where('customers.is_deleted', 0);
            });
        }

        //Filter theo ngày tạo
        if (isset($filter["customer_group_id"]) &&  $filter["customer_group_id"] != null) {
            $ds->where('customers.customer_group_id', $filter["customer_group_id"]);
        }

        return $ds->get();
    }

    public function getAllListCustomerWorkManagement()
    {
        return $this
            ->select(
                'customer_id as id',
                'customer_code as code',
                'full_name',
                DB::raw('CONCAT((CASE WHEN customer_type = "bussiness" THEN "Cá nhân" ELSE "Doanh nghiệp" END),"_",IFNULL(full_name,""),"_",IFNULL(phone1,""),"_",IFNULL(email,"")) as customer_name')
            )
            ->where('is_actived', 1)
            ->where('is_deleted', 0)
            ->orderBy('customer_id', 'DESC')
            ->get();
    }

    public function getItemByPhone($phone)
    {
        $select = $this->select('customer_id', 'customer_code', 'full_name', 'phone1')
            ->where('phone1', $phone)
            ->where('is_actived', 1)
            ->where('is_deleted', 0);
        return $select->first();
    }

    /**
     * Lấy ds KH dựa vào mảng customer ID
     *
     * @param $inArrCustomerId
     * @return mixed
     */
    public function getCustomerByArrCustomerId($inArrCustomerId)
    {
        $select = $this
            ->select(
                'customers.customer_id',
                'customers.full_name',
                'customers.is_actived',
                "customers.customer_code",
                'customers.phone1',
                'customers.birthday',
                'customers.phone1 as phone',
                'customers.gender',
                'branches.branch_name',
                'customers.email'
            )
            ->leftJoin('branches', 'branches.branch_id', '=', 'customers.branch_id')
            ->leftJoin("customer_branch as cb", "cb.customer_id", "=", "{$this->table}.customer_id")
            ->where('customers.customer_id', '!=', self::IS_VANGLAI)
            ->where('customers.is_deleted', self::IS_DELETE)
            ->where('customers.is_actived', self::IS_ACTIVE)
            ->whereIn("customers.customer_id", $inArrCustomerId)
            ->groupBy("{$this->table}.customer_id");

        //Phân quyền xem khách hàng theo chi nhánh
        if (Auth::user()->is_admin != 1 && in_array('permission-customer-branch', session('routeList'))) {
            $select->where("cb.branch_id", Auth::user()->branch_id);
        }

        return $select->get();
    }

    /**
     * Lấy ds KH ko thuộc mảng KH
     *
     * @param $arrCustomerId
     * @return mixed
     */
    public function getCustomerNotInArrCustomerId($arrCustomerId)
    {
        $select = $this
            ->select(
                'customers.customer_id'
            )
            ->leftJoin("customer_branch as cb", "cb.customer_id", "=", "{$this->table}.customer_id")
            ->whereNotIn("customers.customer_id", $arrCustomerId)
            ->where('customers.customer_id', '!=', self::IS_VANGLAI)
            ->where('customers.is_deleted', self::IS_DELETE)
            ->where('customers.is_actived', self::IS_ACTIVE)
            ->groupBy("{$this->table}.customer_id");

        //Phân quyền xem khách hàng theo chi nhánh
        if (Auth::user()->is_admin != 1 && in_array('permission-customer-branch', session('routeList'))) {
            $select->where("cb.branch_id", Auth::user()->branch_id);
        }

        return $select->get();
    }

    /**
     * Ds KH không hoạt động (no login app)
     *
     * @return mixed
     */
    public function getCustomerNoLoginApp()
    {
        $select = $this
            ->select(
                'customers.customer_id'
            )
            ->leftJoin("customer_branch as cb", "cb.customer_id", "=", "{$this->table}.customer_id")
            ->where('customers.customer_id', '!=', self::IS_VANGLAI)
            ->where('customers.is_deleted', self::IS_DELETE)
            ->where('customers.is_actived', self::IS_ACTIVE)
            ->where(function ($query) {
                $query->where("phone_verified", 0)
                    ->orWhereNull("date_last_visit");
            })
            ->groupBy("{$this->table}.customer_id");

        //Phân quyền xem khách hàng theo chi nhánh
        if (Auth::user()->is_admin != 1 && in_array('permission-customer-branch', session('routeList'))) {
            $select->where("cb.branch_id", Auth::user()->branch_id);
        }

        return $select->get();
    }

    /**
     * Ds Kh dựa vào rank (member_levels)
     *
     * @param $arrRank
     * @return mixed
     */
    public function getCustomerInArrRank($arrRank)
    {
        $select = $this
            ->select(
                'customers.customer_id'
            )
            ->leftJoin("customer_branch as cb", "cb.customer_id", "=", "{$this->table}.customer_id")
            ->where('customers.customer_id', '!=', self::IS_VANGLAI)
            ->where('customers.is_deleted', self::IS_DELETE)
            ->where('customers.is_actived', self::IS_ACTIVE)
            ->whereIn('customers.member_level_id', $arrRank)
            ->groupBy("{$this->table}.customer_id");

        //Phân quyền xem khách hàng theo chi nhánh
        if (Auth::user()->is_admin != 1 && in_array('permission-customer-branch', session('routeList'))) {
            $select->where("cb.branch_id", Auth::user()->branch_id);
        }

        return $select->get();
    }

    /**
     * Ds KH trong khoảng điểm thành viên
     *
     * @param $pointFrom
     * @param $pointTo
     * @return mixed
     */
    public function getCustomerInRangePoint($pointFrom, $pointTo)
    {
        $select = $this
            ->select(
                'customers.customer_id'
            )
            ->leftJoin("customer_branch as cb", "cb.customer_id", "=", "{$this->table}.customer_id")
            ->where('customers.customer_id', '!=', self::IS_VANGLAI)
            ->where('customers.is_deleted', self::IS_DELETE)
            ->where('customers.is_actived', self::IS_ACTIVE)
            ->where('customers.point', '>=', $pointFrom)
            ->where('customers.point', '<=', $pointTo)
            ->groupBy("{$this->table}.customer_id");

        //Phân quyền xem khách hàng theo chi nhánh
        if (Auth::user()->is_admin != 1 && in_array('permission-customer-branch', session('routeList'))) {
            $select->where("cb.branch_id", Auth::user()->branch_id);
        }

        return $select->get();
    }

    /**
     * Lấy dữ liệu export KH của Sie
     *
     * @param $beforeDate
     * @return mixed
     */
    public function getCustomerExportSie($beforeDate)
    {
        return $this
            ->select(
                "{$this->table}.customer_id",
                "{$this->table}.full_name",
                "{$this->table}.phone1",
                "{$this->table}.gender",
                "{$this->table}.member_level_id",
                DB::raw("CONCAT({$this->table}.address, ', ', district.type, ' ', district.name, ', ', province.type, ' ', province.name) 
                    as address"),
                "{$this->table}.birthday",
                "{$this->table}.email",
                "member_levels.name as member_level_name",
                "{$this->table}.note",
                "gr.group_name",
                "{$this->table}.created_at",
                "{$this->table}.point",
                "{$this->table}.point_balance"
            )
            ->leftJoin("province", "province.provinceid", "=", "{$this->table}.province_id")
            ->leftJoin("district", "district.districtid", "=", "{$this->table}.district_id")
            ->leftJoin("member_levels", "member_levels.member_level_id", "=", "{$this->table}.member_level_id")
            ->leftJoin("customer_branch as cb", "cb.customer_id", "=", "{$this->table}.customer_id")
            ->leftJoin("customer_groups as gr", "gr.customer_group_id", "=", "{$this->table}.customer_group_id")
            ->where("{$this->table}.customer_id", '!=', 1)
            ->where("{$this->table}.is_deleted", 0)
            ->whereDate("{$this->table}.created_at", "<", $beforeDate)
            ->groupBy("{$this->table}.customer_id")
            ->get();
    }

    /**
     * Lấy thông tin khách hàng
     *
     * @param $customerId
     * @return mixed
     */
    public function getInfoById($customerId)
    {
        return $this
            ->select(
                'customers.customer_group_id as customer_group_id',
                'group.group_name as group_name',
                'customers.full_name as full_name',
                'customers.customer_code as customer_code',
                'customers.gender as gender',
                'customers.phone1 as phone1',
                'province.name as province_name',
                'province.type as province_type',
                'district.name as district_name',
                'district.type as district_type',
                'customers.address as address',
                'customers.email as email',
                'customers.customer_source_id as customer_source_id',
                'customers.birthday as birthday',
                'source.customer_source_name',
                'customers.customer_refer_id',
                'customers.facebook as facebook',
                'customers.zalo as zalo',
                'customers.note as note',
                'customers.customer_id as customer_id',
                'customers.is_actived as is_actived',
                'customers.phone2 as phone2',
                'customers.customer_avatar as customer_avatar',
                'customers.created_at as created_at',
                'customers.account_money as account_money',
                'customers.province_id as province_id',
                'customers.district_id as district_id',
                'customers.point as point',
                'customers.member_level_id as member_level_id',
                'member_levels.name as member_level_name',
                'customers.point as point',
                'member_levels.discount as member_level_discount',
                "{$this->table}.postcode",
                "{$this->table}.custom_1",
                "{$this->table}.custom_2",
                "{$this->table}.custom_3",
                "{$this->table}.custom_4",
                "{$this->table}.custom_5",
                "{$this->table}.custom_6",
                "{$this->table}.custom_7",
                "{$this->table}.custom_8",
                "{$this->table}.custom_9",
                "{$this->table}.custom_10",
                "{$this->table}.total_commission"
            )
            ->leftJoin('customer_groups as group', 'group.customer_group_id', '=', 'customers.customer_group_id')
            ->leftJoin('customer_sources as source', 'source.customer_source_id', '=', 'customers.customer_source_id')
            ->leftJoin('province', 'province.provinceid', '=', 'customers.province_id')
            ->leftJoin('district', 'district.districtid', '=', 'customers.district_id')
            ->leftJoin('member_levels', 'member_levels.member_level_id', '=', 'customers.member_level_id')
            ->where('customers.customer_id', $customerId)
            //            ->where('customers.is_actived', self::IS_ACTIVE)
            //            ->where('customers.is_deleted', self::NOT_DELETE)
            ->first();
    }

    /**
     * lấy tất cả các khách hàng
     * @return mixed
     */

    public function getAll()
    {
        return $this->where('is_actived', self::IS_ACTIVE)
            ->where('is_deleted', self::IS_DELETE)
            ->get();
    }

    /**
     * lấy khách hàng theo điều kiện địa chỉ 
     * @param $listAddress 
     * @return mixed
     */

    public function getCustomerByAddress($listAddress)
    {
        $select = $this->select(
            'customers.customer_id'
        );
        foreach ($listAddress as $key => $item) {
            if ($key == 0) {
                $select->where(function ($query) use ($item) {
                    if (!empty($item->province)) {
                        $query->where('province_id', $item->province);
                    }
                    if (!empty($item->district)) {
                        $query->where('district_id', $item->district);
                    }
                    if (!empty($item->ward)) {
                        $query->where('ward_id', $item->ward);
                    }
                });
            } else {
                $select->orWhere(function ($query) use ($item) {
                    if (!empty($item->province)) {
                        $query->where('province_id', $item->province);
                    }
                    if (!empty($item->district)) {
                        $query->where('district_id', $item->district);
                    }
                    if (!empty($item->ward)) {
                        $query->where('ward_id', $item->ward);
                    }
                });
            }
        }
        return $select->get();
    }

    /**
     * lấy khách hàng theo loại 
     * @param $filters 
     * @return mixed
     */

    public function getCustomerByType($filters)
    {
        return $this->whereIn('customer_type', $filters)->get();
    }

    /**
     * lấy khách hàng theo loại 
     * @param $filters 
     * @return mixed
     */

    public function getCustomerBySource($filters)
    {
        return $this->whereIn('customer_source_id', $filters)->get();
    }

    /**
     * lấy khách hàng theo loại 
     * @param $filters 
     * @return mixed
     */

    public function getCustomerByGroup($filters)
    {
        return $this->whereIn('customer_group_id', $filters)->get();
    }
}

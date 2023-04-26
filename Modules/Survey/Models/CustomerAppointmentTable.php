<?php
/**
 * Created by PhpStorm.
 * User: Mr Son
 * Date: 11/12/2018
 * Time: 10:11 AM
 */

namespace Modules\Survey\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use MyCore\Models\Traits\ListTableTrait;

class CustomerAppointmentTable extends Model
{
    const STATUS_CANCEL = 'cancel';
    const STATUS_FINISH = 'finish';

    use ListTableTrait;
    protected $connection = BRAND_CONNECTION;
    protected $table = 'customer_appointments';
    protected $primaryKey = 'customer_appointment_id';
    protected $fillable = [
        'customer_appointment_id',
        'customer_id',
        'customer_refer',
        'date',
        'time',
        'description',
        'status',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'branch_id',
        'customer_appointment_type',
        'appointment_source_id',
        'customer_quantity',
        'customer_appointment_code',
        'total',
        'amount',
        'discount',
        'voucher_code',
        'end_date',
        'end_time',
        'time_type',
        'number_start',
        'number_end'
    ];

    /**
     * @return mixed
     */
    protected function _getList(&$filter = [])
    {
        $ds = $this
            ->leftJoin('customers', 'customers.customer_id', '=', 'customer_appointments.customer_id')
            ->select('customers.full_name as full_name_cus',
                'customers.phone1 as phone1',
                'customer_appointments.date as date_appointment',
                'customer_appointments.created_at as created_at',
                'customer_appointments.customer_refer as customer_refer',
                'customer_appointments.status as status',
                'customer_appointments.time as time_join',
                'customer_appointments.customer_appointment_id as customer_appointment_id');
        if (isset($filter['date']) != "") {
            $arr_filter = explode(" - ", $filter["date"]);

            $from = Carbon::createFromFormat('m/d/Y', $arr_filter[0])->format('Y-m-d');
            $ds->whereDate('customer_appointments.date', $from);
        }
        unset($filter['date']);
        return $ds;
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function add(array $data)
    {
        $add = $this->create($data);
        return $add->customer_appointment_id;
    }

    public function listCalendar($day_now)
    {
        $ds = $this
            ->leftJoin('customers', 'customers.customer_id', '=', 'customer_appointments.customer_id')
            ->select('customers.full_name as full_name_cus',
                'customers.phone1 as phone1',
                'customer_appointments.date as date_appointment',
                'customer_appointments.created_at as created_at',
                'customer_appointments.customer_refer as customer_refer',
                'customer_appointments.status as status',
                'customer_appointments.customer_appointment_id as customer_appointment_id',
                'customer_appointments.time as time',
                'customer_appointments.customer_quantity'
//                DB::raw("COUNT(customer_appointments.date) as number")
            )
            ->where('customer_appointments.date', '>=', $day_now);
//            ->groupBy('customer_appointments.date')

        if (Auth::user()->is_admin != 1) {
            $ds->where('customer_appointments.branch_id', Auth::user()->branch_id);
        }
        return $ds->get();
    }

    public function listDayGroupBy($day)
    {
        $ds = $this
            ->leftJoin('customers', 'customers.customer_id', '=', 'customer_appointments.customer_id')
            ->select('customers.full_name as full_name_cus',
                DB::raw("COUNT(customer_appointments.date) as number")
            )
            ->where('customer_appointments.date', $day)
            ->groupBy('customer_appointments.date');
        if (Auth::user()->is_admin != 1) {
            $ds->where('customer_appointments.branch_id', Auth::user()->branch_id);
        }
        return $ds->get();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getItemDetail($id)
    {
        $ds = $this
            ->leftJoin('customers', 'customers.customer_id', '=', 'customer_appointments.customer_id')
            ->leftJoin('appointment_source', 'appointment_source.appointment_source_id', '=', 'customer_appointments.appointment_source_id')
            ->leftJoin('branches', 'branches.branch_id', '=', 'customer_appointments.branch_id')
            ->select(
                'customers.full_name as full_name_cus',
                'customers.phone1 as phone1',
                'customers.address as address',
                'customer_appointments.date as date_appointment',
                'customers.customer_avatar as customer_avatar',
                'customer_appointments.created_at as created_at',
                'customer_appointments.customer_refer as customer_refer',
                'customer_appointments.status as status',
                'customer_appointments.customer_appointment_id as customer_appointment_id',
                'customer_appointments.date',
                'customer_appointments.time as time',
                'customer_appointments.customer_quantity',
                'customer_appointments.description',
                'customer_appointments.total',
                'customer_appointments.discount',
                'customer_appointments.amount',
                'customer_appointments.voucher_code',
                'appointment_source.appointment_source_name',
                'customer_appointments.customer_appointment_type',
                'customer_appointments.customer_appointment_code as customer_appointment_code',
                'customers.phone2 as phone2',
                'customers.birthday as birthday',
                'customers.gender',
                'customer_appointments.branch_id',
                'customers.customer_id',
                'branches.branch_name'
            )
            ->where('customer_appointments.customer_appointment_id', $id)
            ->get();
        return $ds;
    }

    public function getItemEdit($id)
    {
        $ds = $this
            ->leftJoin('customers', 'customers.customer_id', '=', 'customer_appointments.customer_id')
            ->leftJoin('member_levels', 'member_levels.member_level_id', '=', 'customers.member_level_id')
            ->select('customers.full_name as full_name_cus',
                'customers.phone1 as phone1',
                'customers.customer_id',
                'customers.address as address',
                'customer_appointments.date as date_appointment',
                'customers.customer_avatar as customer_avatar',
                'customer_appointments.created_at as created_at',
                'customer_appointments.customer_refer as customer_refer',
                'customer_appointments.status as status',
                'customer_appointments.customer_appointment_id as customer_appointment_id',
                'customer_appointments.description as description',
                'member_levels.member_level_id',
                'member_levels.name as member_level_name',
                'member_levels.discount as member_level_discount',
                'customers.gender',
                'customer_appointments.date as date_appointment',
                'customer_appointments.customer_appointment_code as customer_appointment_code',
                'customer_appointments.total',
                'customer_appointments.discount',
                'customer_appointments.amount',
                'customer_appointments.voucher_code'
            )
            ->where('customer_appointments.customer_appointment_id', $id)
            ->first();
        return $ds;
    }

    public function getItemRefer($id)
    {
        $ds = $this->leftJoin('customers', 'customers.customer_id', '=', 'customer_appointments.customer_refer')
            ->select('customers.full_name as full_name_refer',
                'customers.phone1 as phone')->where('customer_appointments.customer_appointment_id', $id)
            ->first();
        return $ds;
    }

    /**
     * @param $id
     */
    public function getItemServiceDetail($id)
    {
        $ds = $this->leftJoin('appointment_services as app_sv', 'app_sv.customer_appointment_id', '=', 'customer_appointments.customer_appointment_id')
            ->leftJoin('services', 'services.service_id', '=', 'app_sv.service_id')
            ->select('services.service_name as service_name',
                'services.time as time',
                'app_sv.quantity as quantity',
                'app_sv.service_id as service_id',
                'app_sv.appointment_service_id as appointment_service_id',
                'services.price_standard as price',
                'services.service_code')
            ->where('app_sv.customer_appointment_id', $id)
            ->where('app_sv.is_deleted', 0)
            ->get();
        return $ds;
    }

    public function listDay($day)
    {
        $ds = $this
            ->leftJoin('customers', 'customers.customer_id', '=', 'customer_appointments.customer_id')
            ->select('customers.full_name as full_name_cus',
                'customers.phone1 as phone1',
                'customer_appointments.date as date_appointment',
                'customer_appointments.created_at as created_at',
                'customer_appointments.customer_refer as customer_refer',
                'customer_appointments.status as status',
                'customer_appointments.customer_appointment_id as customer_appointment_id',
                'customer_appointments.time as time',
                'customers.customer_avatar as customer_avatar',
                "{$this->table}.description"
//                DB::raw("COUNT(customers.full_name) as number")
            )
            ->where('customer_appointments.date', $day);
        if (Auth::user()->is_admin != 1) {
            $ds->where('customer_appointments.branch_id', Auth::user()->branch_id);
        }
        $ds->orderBy('customer_appointments.time', 'asc');
        return $ds->get();
    }

    public function listDayStatus($day, $status)
    {
        $ds = $this
            ->leftJoin('customers', 'customers.customer_id', '=', 'customer_appointments.customer_id')
            ->select('customers.full_name as full_name_cus',
                'customers.phone1 as phone1',
                'customer_appointments.date as date_appointment',
                'customer_appointments.created_at as created_at',
                'customer_appointments.customer_refer as customer_refer',
                'customer_appointments.status as status',
                'customer_appointments.customer_appointment_id as customer_appointment_id',
                DB::raw("COUNT(customer_appointments.date) as number"))
            ->where('customer_appointments.date', $day)
            ->where('customer_appointments.status', $status)
            ->orderBy('customer_appointments.time', 'asc')
            ->groupBy('customer_appointments.date');
        if (Auth::user()->is_admin != 1) {
            $ds->where('customer_appointments.branch_id', Auth::user()->branch_id);
        }
        return $ds->get();
    }

    public function listByTime($time, $day, $id)
    {
        $ds = $this
            ->leftJoin('customers', 'customers.customer_id', '=', 'customer_appointments.customer_id')
            ->leftJoin('appointment_services', 'appointment_services.customer_appointment_id', '=', 'customer_appointments.customer_appointment_id')
            ->leftJoin('services', 'services.service_id', '=', 'appointment_services.service_id')
            ->select('customers.full_name as full_name_cus',
                'customers.phone1 as phone1',
                'customer_appointments.date as date_appointment',
                'customer_appointments.created_at as created_at',
                'customer_appointments.customer_refer as customer_refer',
                'customer_appointments.status as status',
                'customer_appointments.customer_appointment_id as customer_appointment_id',
                'services.service_name as service_name')
            ->where('customer_appointments.time', $time)
            ->where('customer_appointments.date', $day)
            ->where('appointment_services.customer_appointment_id', $id)
            ->where('appointment_services.is_deleted', 0)
            ->orderBy('customer_appointments.time', 'asc')
//            ->groupBy('services.service_name')
            ->get();
        return $ds;
    }

    public function listTimeSearch($time, $day)
    {
        $ds = $this
            ->leftJoin('customers', 'customers.customer_id', '=', 'customer_appointments.customer_id')
            ->leftJoin('appointment_services', 'appointment_services.customer_appointment_id', '=', 'customer_appointments.customer_appointment_id')
            ->leftJoin('services', 'services.service_id', '=', 'appointment_services.service_id')
            ->select('customers.full_name as full_name_cus',
                'customers.phone1 as phone1',
                'customer_appointments.date as date_appointment',
                'customer_appointments.created_at as created_at',
                'customer_appointments.customer_refer as customer_refer',
                'customer_appointments.status as status',
                'customer_appointments.customer_appointment_id as customer_appointment_id',
                'services.service_name as service_name')
            ->where('customer_appointments.time', $time)
            ->where('customer_appointments.date', $day)
            ->orderBy('cus_time.time', 'asc')
//            ->groupBy('services.service_name')
            ->get();
        return $ds;
    }

    public function edit(array $data, $id)
    {
        return $this->where('customer_appointment_id', $id)->update($data);
    }

    public function listNameSearch($search, $day)
    {
        $ds = $this
            ->leftJoin('customers', 'customers.customer_id', '=', 'customer_appointments.customer_id')
            ->select('customers.full_name as full_name_cus',
                'customers.phone1 as phone1',
                'customer_appointments.date as date_appointment',
                'customer_appointments.created_at as created_at',
                'customer_appointments.customer_refer as customer_refer',
                'customer_appointments.status as status',
                'customer_appointments.customer_appointment_id as customer_appointment_id',
                'customer_appointments.time as time',
                'customers.customer_avatar as customer_avatar',
                "{$this->table}.description"
            )
            ->where(function ($query) use ($search) {
                $query->where('customers.full_name', 'like', '%' . $search . '%')
                    ->orWhere('customers.phone1', 'like', '%' . $search . '%')
                    ->orWhere('customer_appointments.customer_appointment_code', 'like', '%' . $search . '%');
            })
            ->where('customer_appointments.date', $day)
            ->orderBy('customer_appointments.time', 'asc');
        if (Auth::user()->is_admin != 1) {
            $ds->where('customer_appointments.branch_id', Auth::user()->branch_id);
        }
        return $ds->get();
    }

    public function detailDayCustomer($id)
    {
        $ds = $this->select('date')
            ->where('customer_id', $id)
            ->orderBy("{$this->table}.customer_appointment_id", "desc")
            ->groupBy(DB::raw('Date(date)'))
            ->get();
        return $ds;
    }

    public function detailCustomer($day, $id)
    {
        $ds = $this->leftJoin('branches', 'branches.branch_id', '=', 'customer_appointments.branch_id')
            ->select(
                'customer_appointments.customer_appointment_id',
                'customer_appointments.date',
                'customer_appointments.time',
                'customer_appointments.status',
                'customer_appointments.customer_quantity',
                'customer_appointments.description'
            )
            ->where(DB::raw('Date(customer_appointments.date)'), $day)
            ->where('customer_appointments.customer_id', $id)
            ->orderBy('customer_appointments.date', 'DESC')
            ->orderBy('customer_appointments.time', 'DESC')->get();
        return $ds;
    }

    /**
     * @param $year
     * @param $status
     * @param $branch
     * @return mixed
     * Thống kê lịch hẹn theo năm hiện tại của tất cả chi nhánh
     */
    public function reportYearAllBranch($year, $status, $branch)
    {
        $ds = $this->select('date', DB::raw('count(date) as number'))
            ->whereYear('date', '=', $year)
            ->where('status', $status);
        if ($branch != null) {
            $ds->where('branch_id', $branch);
        }
        return $ds->get();
    }

    /**
     * @param $year
     * @param $branch
     * @return mixed
     * Thống kê nguồn lịch hẹn theo chi nhánh
     */
    public function reportAppointmentSource($year, $branch)
    {
        $ds = $this->leftJoin('appointment_source', 'appointment_source.appointment_source_id', '=',
            'customer_appointments.appointment_source_id')
            ->select('appointment_source.appointment_source_name',
                DB::raw('count(customer_appointments.appointment_source_id) as number_appointment_source'))
            ->whereYear('customer_appointments.date', '=', $year);
        if ($branch != null) {
            $ds->where('customer_appointments.branch_id', $branch);
        }
        return $ds->groupBy('customer_appointments.appointment_source_id')->get();
    }

    /**
     * @param $year
     * @param $branch
     * @return mixed
     * Thống kê giới tính khách hàng theo chi nhánh
     */
    public function reportGenderBranch($year, $branch)
    {
        $ds = $this->leftJoin('customers', 'customers.customer_id', '=', 'customer_appointments.customer_id')
            ->select('customers.gender', DB::raw('count(customers.gender) as number'))
            ->whereYear('customer_appointments.date', '=', $year);
        if ($branch != null) {
            $ds->where('customer_appointments.branch_id', $branch);
        }
        return $ds->groupBy('customers.gender')->get();
    }

    public function reportCustomerSourceBranch($year, $branch)
    {
        $ds = $this->leftJoin('customers', 'customers.customer_id', '=', 'customer_appointments.customer_id')
            ->leftJoin('customer_sources', 'customer_sources.customer_source_id', '=', 'customers.customer_source_id')
            ->select('customer_sources.customer_source_name',
                DB::raw('count(customer_sources.customer_source_id) as number'))
            ->whereYear('customer_appointments.date', '=', $year);
        if ($branch != null) {
            $ds->where('customer_appointments.branch_id', $branch);
        }
        return $ds->groupBy('customer_sources.customer_source_id')->get();
    }

    /**
     * @param $year
     * @param $month
     * @param $status
     * @param $branch
     * @return mixed
     * Thống kê lịch hẹn theo năm, tháng theo chi nhánh
     */
    public function reportMonthYearBranch($year, $month, $status, $branch)
    {
        $ds = $this->select('date', DB::raw('count(date) as number'))
            ->whereYear('date', '=', $year)->whereMonth('date', '=', $month)
            ->where('status', $status);
        if ($branch != null) {
            $ds->where('branch_id', $branch);
        }
        return $ds->get();
    }

    /**
     * @param $time
     * @param $status
     * @param $branch
     * @return mixed
     * Thống kê từ ngày đến ngày theo tất cả chi nhánh
     */
    public function reportTimeAllBranch($time, $status, $branch)
    {
        $arr_filter = explode(" - ", $time);
        $startTime = Carbon::createFromFormat('d/m/Y', $arr_filter[0])->format('Y-m-d');
        $endTime = Carbon::createFromFormat('d/m/Y', $arr_filter[1])->format('Y-m-d');
        $ds = $this->select('date', DB::raw('count(date) as number'))
            ->whereBetween('date', [$startTime, $endTime])
            ->where('status', $status);
        if ($branch != null) {
            $ds->where('branch_id', $branch);
        }
        return $ds->get();
    }

    /**
     * @param $year
     * @param $branch
     * @return mixed
     * Thống kê nguồn lịch hẹn theo chi nhánh
     */
    public function reportTimeAppointmentSource($time, $branch)
    {
        $arr_filter = explode(" - ", $time);
        $startTime = Carbon::createFromFormat('d/m/Y', $arr_filter[0])->format('Y-m-d');
        $endTime = Carbon::createFromFormat('d/m/Y', $arr_filter[1])->format('Y-m-d');
        $ds = $this->leftJoin('appointment_source', 'appointment_source.appointment_source_id', '=',
            'customer_appointments.appointment_source_id')
            ->select('appointment_source.appointment_source_name',
                DB::raw('count(customer_appointments.appointment_source_id) as number_appointment_source'))
            ->whereBetween('date', [$startTime, $endTime]);
        if ($branch != null) {
            $ds->where('customer_appointments.branch_id', $branch);
        }
        return $ds->groupBy('customer_appointments.appointment_source_id')->get();
    }

    /**
     * @param $year
     * @param $branch
     * @return mixed
     * Thống kê giới tính khách hàng theo chi nhánh
     */
    public function reportTimeGenderBranch($time, $branch)
    {
        $arr_filter = explode(" - ", $time);
        $startTime = Carbon::createFromFormat('d/m/Y', $arr_filter[0])->format('Y-m-d');
        $endTime = Carbon::createFromFormat('d/m/Y', $arr_filter[1])->format('Y-m-d');
        $ds = $this->leftJoin('customers', 'customers.customer_id', '=', 'customer_appointments.customer_id')
            ->select('customers.gender', DB::raw('count(customers.gender) as number'))
            ->whereBetween('customer_appointments.date', [$startTime, $endTime]);
        if ($branch != null) {
            $ds->where('customer_appointments.branch_id', $branch);
        }
        return $ds->groupBy('customers.gender')->get();
    }

    public function reportTimeCustomerSourceBranch($time, $branch)
    {
        $arr_filter = explode(" - ", $time);
        $startTime = Carbon::createFromFormat('d/m/Y', $arr_filter[0])->format('Y-m-d');
        $endTime = Carbon::createFromFormat('d/m/Y', $arr_filter[1])->format('Y-m-d');
        $ds = $this->leftJoin('customers', 'customers.customer_id', '=', 'customer_appointments.customer_id')
            ->leftJoin('customer_sources', 'customer_sources.customer_source_id', '=', 'customers.customer_source_id')
            ->select('customer_sources.customer_source_name',
                DB::raw('count(customer_sources.customer_source_id) as number'))
            ->whereBetween('customer_appointments.date', [$startTime, $endTime]);
        if ($branch != null) {
            $ds->where('customer_appointments.branch_id', $branch);
        }
        return $ds->groupBy('customer_sources.customer_source_id')->get();
    }

    /**
     * @param $date
     * @param $status
     * @param $branch
     * @return mixed
     * Thống kê từ ngày đến ngày theo 1 chi nhánh
     */
    public function reportDateBranch($date, $status, $branch)
    {
//        if (Auth::user()->is_admin != 1) {
//            $branch = Auth::user()->branch_id;
//        }
        $ds = $this->select('date', DB::raw('count(date) as number'))
            ->where('date', '=', $date)
            ->where('status', $status);
        if ($branch != null) {
            $ds->where('branch_id', $branch);
        }
        return $ds->get();
    }

//    public function getNewAppointments()
//    {
//
//    }
    //Lất tất cả lịch hẹn của hôm nay.
    public function getCustomerAppointmentTodays()
    {
        $select = $this->select(
            'customer_appointments.customer_appointment_id as customer_appointment_id',
            'customers.full_name as full_name_cus',
            'customers.phone1 as phone1',
            'customer_appointments.date as date_appointment',
            'customer_appointments.created_at as created_at',
            'customer_appointments.time as time',
            'customer_appointments.customer_appointment_code as customer_appointment_code',
            'customers.phone2 as phone2',
            'customers.gender as gender',
            'customer_appointments.date as date'
        )
            ->leftJoin('customers', 'customers.customer_id', '=', 'customer_appointments.customer_id')
            ->where('customer_appointments.status', 'confirm')
            ->where('date', date('Y-m-d'))
            ->where('customer_appointments.time', '>=', date('H:i'))
            ->get();
        return $select;
    }

    //search dashboard
    public function searchDashboard($keyword)
    {
        $time = Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now()->subDays(30))->format('Y-m-d');
        $select = $this->leftJoin('customers', 'customers.customer_id', '=', 'customer_appointments.customer_id')
            ->select(
                'customer_appointments.customer_appointment_id as customer_appointment_id',
                'customer_appointments.customer_appointment_code as customer_appointment_code',
                'customers.full_name as full_name',
                'customers.phone1 as phone1',
                'customer_appointments.status as status',
                'customer_appointments.date as date',
                'customer_appointments.time as time',
                'customers.customer_avatar as customer_avatar'
            )
            ->where(function ($query) use ($keyword) {
                $query->where('customers.full_name', 'like', '%' . $keyword . '%')
                    ->orWhere('customers.phone1', 'like', '%' . $keyword . '%')
                    ->orWhere('customers.email', 'like', '%' . $keyword . '%')
                    ->orWhere('customer_appointments.customer_appointment_code', 'like', '%' . $keyword . '%');
            })
            ->where('customers.is_deleted', 0)
            ->where('customers.is_actived', 1)
            ->where('customer_appointments.status', '<>', 'cancel')
            ->where('customer_appointments.created_at', '>', $time . ' 00:00:00')
            ->orderBy('customer_appointments.date', 'desc');
        if (Auth::user()->is_admin != 1) {
            $select->where('customer_appointments.branch_id', Auth::user()->branch_id);
        };
        return $select->get();

    }

    public function reportTimeGenderBranch2($time, $branch)
    {
        $arr_filter = explode(" - ", $time);
        $startTime = Carbon::createFromFormat('d/m/Y', $arr_filter[0])->format('Y-m-d');
        $endTime = Carbon::createFromFormat('d/m/Y', $arr_filter[1])->format('Y-m-d');
        $ds = $this->leftJoin('customers', 'customers.customer_id', '=', 'customer_appointments.customer_id')
            ->select('customers.gender', DB::raw('count(customer_appointments.customer_appointment_id) as number'))
            ->whereBetween('customer_appointments.date', [$startTime, $endTime]);
        if ($branch != null) {
            $ds->where('customer_appointments.branch_id', $branch);
        }
        return $ds->groupBy('customers.gender')->get();
    }

    protected function _getListCancel($filter = [])
    {
        $ds = $this
            ->leftJoin('customers', 'customers.customer_id', '=', 'customer_appointments.customer_id')
            ->leftJoin('appointment_source', 'appointment_source.appointment_source_id', '=', 'customer_appointments.appointment_source_id')
            ->select(
                'customers.full_name as full_name',
                'customers.phone1 as phone1',
                'customer_appointments.date',
                'customer_appointments.status',
                'customer_appointments.time',
                'customer_appointments.customer_appointment_id',
                'customer_appointments.customer_appointment_code as customer_appointment_code',
                'customer_appointments.customer_appointment_type as customer_appointment_type',
                'customer_appointments.customer_quantity',
                'customer_appointments.branch_id',
                'appointment_source.appointment_source_name')
            ->where('customer_appointments.branch_id', Auth::user()->branch_id)
            ->where('customer_appointments.status', 'cancel')
            ->orderBy('customer_appointments.date', 'asc')
            ->orderBy('customer_appointments.time', 'asc');
        if (isset($filter['search']) != "") {
            $search = $filter['search'];
            $ds->where(function ($query) use ($search) {
                $query
                    ->where('customers.full_name', 'like', '%' . $search . '%')
                    ->orWhere('customers.phone1', 'like', '%' . $search . '%')
                    ->orWhere('customer_appointments.customer_appointment_code', 'like', '%' . $search . '%');
            });
        }
        if (isset($filter["created_at"]) != "") {
            $arr_filter = explode(" - ", $filter["created_at"]);
            $startTime = Carbon::createFromFormat('d/m/Y', $arr_filter[0])->format('Y-m-d');
            $endTime = Carbon::createFromFormat('d/m/Y', $arr_filter[1])->format('Y-m-d');
            $ds->whereBetween('customer_appointments.date', [$startTime, $endTime]);
        }
        return $ds;
    }

    public function getListCancel(array $filter = [])
    {
        $select = $this->_getListCancel($filter);

        $page = (int)($filter['page'] ?? 1);
        $display = (int)($filter['perpage'] ?? PAGING_ITEM_PER_PAGE);
        // search term
        if (!empty($filter['search_type']) && !empty($filter['search_keyword'])) {
            $select->where($filter['search_type'], 'like', '%' . $filter['search_keyword'] . '%');
        }
        unset($filter['search_type'], $filter['search_keyword'], $filter['page'], $filter['display'],
            $filter['search'], $filter["created_at"], $filter["birthday"]);

        // filter list
        foreach ($filter as $key => $val) {
            if (trim($val) == '') {
                continue;
            }

            $select->where(str_replace('$', '.', $key), $val);
        }

        return $select->paginate($display, $columns = ['*'], $pageName = 'page', $page);
    }

    public function _getListLate($filter = [])
    {
        $ds = $this
            ->leftJoin('customers', 'customers.customer_id', '=', 'customer_appointments.customer_id')
            ->leftJoin('appointment_source', 'appointment_source.appointment_source_id', '=', 'customer_appointments.appointment_source_id')
            ->select(
                'customers.full_name as full_name',
                'customers.phone1 as phone1',
                'customer_appointments.date',
                'customer_appointments.status',
                'customer_appointments.time',
                'customer_appointments.customer_appointment_id',
                'customer_appointments.customer_appointment_code as customer_appointment_code',
                'customer_appointments.customer_appointment_type as customer_appointment_type',
                'customer_appointments.customer_quantity',
                'customer_appointments.branch_id',
                'appointment_source.appointment_source_name')
            ->where('customer_appointments.branch_id', Auth::user()->branch_id)
            ->whereIn('customer_appointments.status', ['new', 'confirm', 'finish', 'wait'])
            ->whereDate('customer_appointments.date', '<=', date('Y-m-d'))
            ->whereTime('customer_appointments.time', '<=', date('H:i'))
            ->orderBy('customer_appointments.date', 'desc');
        if (isset($filter['search']) != "") {
            $search = $filter['search'];
            $ds->where(function ($query) use ($search) {
                $query
                    ->where('customers.full_name', 'like', '%' . $search . '%')
                    ->orWhere('customers.phone1', 'like', '%' . $search . '%')
                    ->orWhere('customer_appointments.customer_appointment_code', 'like', '%' . $search . '%');
            });
        }
        if (isset($filter["created_at"]) != "") {
            $arr_filter = explode(" - ", $filter["created_at"]);
            $startTime = Carbon::createFromFormat('d/m/Y', $arr_filter[0])->format('Y-m-d');
            $endTime = Carbon::createFromFormat('d/m/Y', $arr_filter[1])->format('Y-m-d');
            $ds->whereBetween('customer_appointments.date', [$startTime, $endTime]);
        }
        return $ds;
    }

    public function getListLate(array $filter = [])
    {
        $select = $this->_getListLate($filter);
        $page = (int)($filter['page'] ?? 1);
        $display = (int)($filter['perpage'] ?? PAGING_ITEM_PER_PAGE);
        // search term
        if (!empty($filter['search_type']) && !empty($filter['search_keyword'])) {
            $select->where($filter['search_type'], 'like', '%' . $filter['search_keyword'] . '%');
        }
        unset($filter['search_type'], $filter['search_keyword'], $filter['page'], $filter['display'],
            $filter['search'], $filter["created_at"], $filter["birthday"]);

        // filter list
        foreach ($filter as $key => $val) {
            if (trim($val) == '') {
                continue;
            }

            $select->where(str_replace('$', '.', $key), $val);
        }

        return $select->paginate($display, $columns = ['*'], $pageName = 'page', $page);
    }

    /**
     * Kiểm tra số lượng đặt lịch trong ngày
     *
     * @param $customer_id
     * @param $date
     * @param $type
     * @param $branchId
     * @return mixed
     */
    public function checkNumberAppointment($customer_id, $date, $type, $branchId)
    {
        $ds = $this
            ->select('customer_appointment_id',
                'customer_id',
                'appointment_source_id',
                'customer_appointment_type',
                'date',
                'time',
                'customer_quantity',
                'description',
                'status'
            )
            ->where(function ($query) use ($customer_id) {
                $query->where('customer_id', $customer_id);
            })
            ->where('date', $date)
            ->where('branch_id', $branchId)
            ->whereNotIn('status', ['finish', 'cancel']);
        if ($type == 'check') {
            $ds->orderBy('time', 'asc');
        }
        if ($type == 'update') {
            $ds->orderBy('customer_appointment_id', 'desc');
        }
        return $ds->get();
    }

    public function checkExistsAppointment($customer_id, $date, $time, $endDate, $endTime, $type, $branchId)
    {
        $ds = $this
            ->select('customer_appointment_id',
                'customer_id',
                'appointment_source_id',
                'customer_appointment_type',
                'date',
                'time',
                'customer_quantity',
                'description',
                'status'
            )
            ->where(function ($query) use ($customer_id) {
                $query->where('customer_id', $customer_id);
            })
            ->whereDate('date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->whereTime('time', '<=', $time)
            ->whereTime('end_time', '>=', $time)
            ->where('branch_id', $branchId)
            ->whereNotIn('status', ['finish', 'cancel']);
        if ($type == 'check') {
            $ds->orderBy('time', 'asc');
        }
        if ($type == 'update') {
            $ds->orderBy('customer_appointment_id', 'desc');
        }
        return $ds->get();
    }

    public function checkExistsAppointmentService($date, $time, $endDate, $endTime, $type, $branchId, $arrService, $customer_appointment_id = '')
    {
        $ds = $this
            ->select('customer_appointments.customer_appointment_id',
                'customer_appointments.customer_id',
                'customer_appointments.appointment_source_id',
                'customer_appointments.customer_appointment_type',
                'customer_appointments.date',
                'customer_appointments.time',
                'customer_appointments.customer_quantity',
                'customer_appointments.description',
                'customer_appointments.status'
            )
            ->leftJoin("customer_appointment_details", "customer_appointment_details.customer_appointment_id", "customer_appointments.customer_appointment_id")
            ->where(DB::raw("CONCAT(`date`,' ',`time`)"), "<=", $date . ' ' . $time)
            ->where(DB::raw("CONCAT(`end_date`,' ',`end_time`)"), ">=", $date . ' ' . $time)
            ->where('branch_id', $branchId)
            ->whereNotIn('status', ['finish', 'cancel']);
        if(count($arrService) > 0){
            $ds->whereIn("customer_appointment_details.object_id", $arrService)
                ->where("customer_appointment_details.object_type", "service");
        }
        if ($type == 'update') {
            $ds->where('customer_appointments.customer_appointment_id','<>', $customer_appointment_id);
        }
        $ds->groupBy("customer_appointments.customer_appointment_id");
        return $ds->get();
    }
    /**
     * Danh sách KH từ ngày .... now
     * @param $day
     * @return mixed
     */
    public function getCustomerAppointmentDayTo($day)
    {
        $select = $this->select(
            'customer_appointments.customer_id as customer_id'
        )
            ->join('customers', 'customers.customer_id', '=', 'customer_appointments.customer_id')
            ->where('customers.is_deleted', 0)
            ->where('customers.is_actived', 1)
            ->where('customers.customer_id','!=', 1)
            ->where('date', '>', $day)->get();
        return $select;
    }

    /**
     * Lấy danh sách KH theo trạng thái lịch hẹn
     * @param $status
     * @return mixed
     */
    public function getCustomerAppointmentByStatus($status)
    {
        $select = $this->select(
            'customer_appointments.customer_id as customer_id'
        )
            ->join('customers', 'customers.customer_id', '=', 'customer_appointments.customer_id')
            ->where('customers.is_deleted', 0)
            ->where('customers.is_actived', 1)
            ->where('customers.customer_id','!=', 1)
            ->where('status', $status)->get();
        return $select;
    }

    /**
     *
     * @param $day
     * @return mixed
     */
    public function getCustomerAppointmentByTime($timeFrom, $timeTo)
    {
        $select = $this->select(
            'customer_appointments.customer_id as customer_id',
            'time'
        )
            ->join('customers', 'customers.customer_id', '=', 'customer_appointments.customer_id')
            ->whereBetween('customer_appointments.time', [$timeFrom, $timeTo])
            ->where('customers.is_deleted', 0)
            ->where('customers.is_actived', 1)
            ->where('customers.customer_id','!=', 1)
            ->get();
        return $select;
    }

    /**
     * Kiểm tra khách hàng đã đặt lịch giờ đó chưa
     *
     * @param $customerId
     * @param $date
     * @param $time
     * @param $branchId
     * @param $appointmentId
     * @return mixed
     */
    public function checkDateTimeCustomer($customerId, $date, $time, $branchId, $appointmentId)
    {
        return $this
            ->select(
                "customer_appointment_id",
                "customer_appointment_code",
                "date",
                "time"
            )
            ->where("customer_id", $customerId)
            ->where("date", $date)
            ->where("time", $time)
            ->where("branch_id", $branchId)
            ->where("customer_appointment_id", "<>", $appointmentId)
            ->whereNotIn("status", ['cancel'])
            ->first();
    }

    /**
     * Lấy thông tin lịch hẹn
     *
     * @param $appointmentId
     * @return mixed
     */
    public function getInfo($appointmentId)
    {
        $data =  $this
            ->select(
                "customers.full_name",
                "customers.customer_group_id",
                DB::raw("IFNULL(customer_groups.group_name,'') as group_name"),
                "customers.phone1 as phone",
                "customers.address as address",
                "customers.customer_id",
                "{$this->table}.customer_appointment_id",
                "{$this->table}.date",
                "{$this->table}.time",
                "{$this->table}.status as status",
                "{$this->table}.customer_quantity",
                "{$this->table}.description",
                "{$this->table}.total",
                "{$this->table}.discount",
                "{$this->table}.amount",
                "{$this->table}.voucher_code",
                "appointment_source.appointment_source_name",
                "{$this->table}.customer_appointment_type",
                "{$this->table}.customer_appointment_code",
                "{$this->table}.branch_id",
                "{$this->table}.end_date",
                "{$this->table}.end_time",
                "branches.branch_name",
                "{$this->table}.description",
                "{$this->table}.time_type",
                "{$this->table}.number_start",
                "{$this->table}.number_end"
            )
            ->join("customers", "customers.customer_id", "=", "{$this->table}.customer_id")
            ->leftJoin("customer_groups", "customer_groups.customer_group_id", "=", "customers.customer_group_id")
            ->leftJoin("appointment_source", "appointment_source.appointment_source_id", "=", "{$this->table}.appointment_source_id")
            ->join("branches", "branches.branch_id", "=", "{$this->table}.branch_id")
            ->where("{$this->table}..customer_appointment_id", $appointmentId)
            ->first();
        return $data;
    }


    /**
     * Danh sach booking dich vu
     *
     * @param $arrSrvId
     * @param $start
     * @param $endDate
     * @param $searchKeyword
     * @return mixed
     */
    public function bookingCalendar($arrSrvId, $start, $endDate, $searchKeyword = null)
    {
        $select = $this->select
        (
            "{$this->table}.customer_appointment_id",
            'service_id',
            'date',
            'time',
            'end_date',
            'end_time',
            "{$this->table}.status"
        )
//            ->join("customers", "customers.customer_id", "=", "{$this->table}.customer_id")
            ->join('customer_appointment_details as ad', function ($join) use ($arrSrvId) {
                $join->on("ad.{$this->primaryKey}", "{$this->table}.{$this->primaryKey}")
                    ->whereIn('service_id', $arrSrvId)
                    ->whereNotIn("{$this->table}.status", [self::STATUS_CANCEL, self::STATUS_FINISH]);
            })
//            ->where('date', '>=', $start)
//            ->orWhere(function ($cond) use ($start, $endDate) {
//                $cond->where('end_date', '>=', $start)
//                    ->where('end_date', '<=', $endDate);
//            })
            ->where(function ($query) use ($start) {
                $query->where(DB::raw('date'), '>=', $start)
                    ->orWhere(DB::raw('end_date'), '>=', $start);
            })->orWhere(function ($query) use ($endDate) {
                $query->where(DB::raw('date'), '<=', $endDate)
                    ->orWhere(DB::raw('end_date'), '<=', $endDate);
            });;

//        if ($searchKeyword != null) {
//            $select->where(function ($query) use ($searchKeyword) {
//                $query->where('customers.full_name', 'like', '%' . $searchKeyword . '%')
//                    ->orWhere('customers.phone1', 'like', '%' . $searchKeyword . '%');
//            });
//        }
        return $select->get();
    }

    /**
     * Lấy ds lịch sử đặt lịch theo số điện thoại
     *
     * @param $phone
     * @return mixed
     */
    public function getHistoryAppointmentByPhone($filter){
        $select = $this->select(
//            DB::raw("(ROW_NUMBER() OVER (ORDER BY created_at DESC)) as row_num"),
            "{$this->table}.customer_appointment_id",
            "{$this->table}.date",
            "{$this->table}.time",
            "{$this->table}.end_date",
            "{$this->table}.end_time",
            "{$this->table}.status",
//            DB::raw("(GROUP_CONCAT(DISTINCT CONCAT(services.service_name,', ',service_cards.service_card_name))) as object_name_2"),
            DB::raw("IFNULL((GROUP_CONCAT(CASE WHEN customer_appointment_details.object_type = 'service' 
                                                    THEN services.service_name
                                              ELSE service_cards.name END)),".__("'Không có dịch vụ'").") as object_name"))
            ->leftJoin("customers","customers.customer_id","{$this->table}.customer_id")
            ->leftJoin("customer_appointment_details","customer_appointment_details.customer_appointment_id","{$this->table}.customer_appointment_id")
            ->leftJoin("services",function($join){
                $join->on('services.service_id', '=', 'customer_appointment_details.object_id')
                    ->where('customer_appointment_details.object_type', '=', 'service');
            })
            ->leftJoin("customer_service_cards",function($join){
                $join->on('customer_service_cards.customer_service_card_id', '=', 'customer_appointment_details.object_id')
                    ->where('customer_appointment_details.object_type', '=', 'member_card');

            })
            ->leftJoin("service_cards", "service_cards.service_card_id", "=", "customer_service_cards.service_card_id")
            ->where("status","!=","cancel")
            ->where("{$this->table}.customer_id","=",$filter['customer_id'])
            ->groupBy("{$this->table}.customer_appointment_id","customer_appointment_details.object_id")
        ->orderBy("{$this->table}.created_at","DESC");
        $page = (int)($filter['page'] ?? 1);
        $display = 5;
        // search term
        if (!empty($filter['search_type']) && !empty($filter['search_keyword'])) {
            $select->where($filter['search_type'], 'like', '%' . $filter['search_keyword'] . '%');
        }
        unset($filter['search_type'], $filter['search_keyword'], $filter['page'], $filter['display'],
            $filter['search'], $filter["created_at"], $filter["birthday"]);

        return $select->paginate($display, $columns = ['*'], $pageName = 'page', $page);
    }
    public function getServiceNameOfAppointment($customerAppointmentId){
        $data = $this->select("customer_appointment_details.object_name")
            ->leftJoin("customer_appointment_details","customer_appointment_details.customer_appointment_id","{$this->table}.customer_appointment_id")
            ->where("{$this->table}.customer_appointment_id",$customerAppointmentId)
            ->where("customer_appointment_details.object_type","=","service");
        return $data->get()->toArray();
    }

    /**
     * Ds khách hàng có lịch hẹn cách đây $day ngày
     *
     * @param $day
     * @return mixed
     */
    public function getCustomerDayAppointment($day){
        $startTime = Carbon::now()->day(-$day)->format("Y-m-d 00:00:00");
        $endTime = Carbon::now()->format("Y-m-d 23:59:59");
        $data = $this->select(
            "customer_appointment_id",
            "customer_id"
        )
        ->whereBetween("date",[$startTime , $endTime])
        ->where('customers.is_deleted', 0)
        ->where('customers.is_actived', 1);
        return $data->get()->toArray();
    }
}

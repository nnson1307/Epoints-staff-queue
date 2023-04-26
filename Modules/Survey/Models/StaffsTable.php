<?php

/**
 * Created by PhpStorm.
 * User: Mr Son
 * Date: 9/29/2018
 * Time: 10:37 AM
 */

namespace Modules\Survey\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use MyCore\Models\Traits\ListTableTrait;

class StaffsTable extends Model
{
    use ListTableTrait;
    protected $connection = BRAND_CONNECTION;
    protected $table = 'staffs';
    protected $primaryKey = 'staff_id';
    protected $fillable = [
        'staff_id', 'department_id', 'branch_id', 'staff_title_id', 'user_name', 'password', 'salt', 'full_name',
        'birthday', 'gender', 'phone1', 'phone2', 'email', 'facebook', 'date_last_login', 'is_admin', 'is_actived',
        'is_deleted', 'staff_avatar', 'address', 'created_by', 'updated_by', 'created_at', 'updated_at', 'remember_token',
        'staff_code', 'salary', 'subsidize', 'commission_rate', 'staff_type', 'password_chat'
    ];

    /**
     * @return mixed
     */
    protected function _getList($filter = [])
    {
        $ds = $this->leftJoin('departments', 'departments.department_id', '=', 'staffs.department_id')
            ->leftJoin('branches', 'branches.branch_id', '=', 'staffs.branch_id')
            ->leftJoin('staff_title', 'staff_title.staff_title_id', '=', 'staffs.staff_title_id')
            ->select(
                'staffs.staff_id as staff_id',
                'departments.department_name as department_name',
                'branches.branch_name as branch_name',
                'staff_title.staff_title_name as staff_title_name',
                'staffs.user_name as account',
                'staffs.salt as salt',
                'staffs.full_name as name',
                'staffs.birthday as birthday',
                'staffs.gender as gender',
                'staffs.phone1 as phone1',
                'staffs.phone2 as phone2',
                'staffs.email as email',
                'staffs.facebook as facebook',
                'staffs.date_last_login as date_last_login',
                'staffs.is_admin as is_admin',
                'staffs.is_actived as is_actived',
                'staffs.staff_avatar as staff_avatar',
                'staffs.address as address'
            )
            ->where('staffs.is_deleted', 0)
            ->where('staffs.is_master', 0)
            ->orderBy('staffs.staff_id', 'desc');
        if (isset($filter['search']) != "") {
            $search = $filter['search'];
            $ds->where(function ($query) use ($search) {
                $query->where('staffs.full_name', 'like', '%' . $search . '%')
                    ->orWhere('staffs.user_name', 'like', '%' . $search . '%')
                    ->orWhere('staffs.email', 'like', '%' . $search . '%')
                    ->where('staffs.is_deleted', 0);
            });
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
        return $add->staff_id;
    }
    //function xoa

    /**
     * @param $id
     */
    public function remove($id)
    {
        $this->where($this->primaryKey, $id)->update(['is_deleted' => 1]);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getItem($id)
    {
        return $this
            ->select(
                'staffs.*',
                'departments.department_name as department_name',
                'branches.branch_name as branch_name',
                'staff_title.staff_title_name as staff_title_name',
                'staffs.user_name as account',
                'staffs.salt as salt',
                'staffs.staff_type',
                'staffs.full_name as name',
                'staffs.birthday as birthday',
                'staffs.gender as gender',
                'staffs.phone1 as phone1',
                'staffs.phone2 as phone2',
                'staffs.email as email',
                'staffs.facebook as facebook',
                'staffs.date_last_login as date_last_login',
                'staffs.is_admin as is_admin',
                'staffs.is_actived as is_actived',
                'staffs.staff_avatar as staff_avatar',
                'staffs.address as address',
                'staffs.salary as salary',
                'staffs.subsidize as subsidize',
                'staffs.commission_rate as commission_rate'
            )
            ->leftJoin('departments', 'departments.department_id', '=', 'staffs.department_id')
            ->leftJoin('branches', 'branches.branch_id', '=', 'staffs.branch_id')
            ->leftJoin('staff_title', 'staff_title.staff_title_id', '=', 'staffs.staff_title_id')
            ->where($this->primaryKey, $id)
            ->first();
    }

    /**
     * @param array $data
     * @param $id
     * @return mixed
     */
    public function edit(array $data, $id)
    {
        return $this->where($this->primaryKey, $id)->update($data);
    }

    /**
     * @param $userName
     * @param $id
     * @return mixed
     */
    public function testUserName($userName, $id)
    {
        return $this->where('user_name', $userName)->where('staff_id', '<>', $id)->where('is_deleted', 0)->first();
    }

    /**
     * @return array
     */
    public function getName()
    {
        $oSelect = self::select("staff_id", "full_name")->where('is_deleted', 0)->get();
        return (["" => "Tất cả"]) + ($oSelect->pluck("full_name", "staff_id")->toArray());
    }

    /**
     * @return mixed
     */
    public function getStaffOption()
    {
        return $this->select('staff_id', 'full_name', 'address', 'phone1', 'phone2')->where('is_deleted', 0)->get()->toArray();
    }

    public function getStaffTechnician()
    {
        $ds = $this->select(
            'staff_id',
            'full_name',
            'address',
            'phone1',
            'phone2'
        )
            ->where('is_deleted', 0)
            //            ->where('staff_title_id', 2)
            ->where('branch_id', Auth::user()->branch_id)
            ->get()
            ->toArray();
        return $ds;
    }

    public function getNameStaff($id)
    {
        return $this->where('staff_id', $id)->where('is_deleted', 0)->first();
    }

    /**
     * Lấy thông tin tất cả nhân viên
     *
     * @return mixed
     */
    public function getAllStaff()
    {
        return $this
            ->select(
                "{$this->table}.full_name",
                "{$this->table}.phone1 as phone",
                "{$this->table}.address",
                "branches.branch_name",
                "staff_title.staff_title_name",
                "{$this->table}.salary",
                "{$this->table}.subsidize",
                "{$this->table}.commission_rate"
            )
            ->leftJoin("branches", "branches.branch_id", "=", "{$this->table}.branch_id")
            ->join("staff_title", "staff_title.staff_title_id", "=", "{$this->table}.staff_title_id")
            ->get();
    }

    /**
     * Lấy thông tin hoa hồng của nhân viên
     *
     * @param $idStaff
     * @return mixed
     */
    public function getCommissionStaff($idStaff)
    {
        return $this
            ->select(
                "staff_id",
                "commission_rate"
            )
            ->where("staff_id", $idStaff)
            ->where('is_deleted', 0)
            ->first();
    }

    

    /**
     * lấy tất cả nhân viên 
     * @return mixed
     */

    public function getAll()
    {
        return $this->where("is_actived", 1)
            ->where("is_deleted", 0)
            ->orderBy('staff_id', 'DESC')
            ->get();
    }


    /**
     * lấy tất cả nhân viên theo điều kiện động
     * @param $filter
     * @param $type
     * @return mixed
     */

    public function getAllByConditionAuto($filters, $type)
    {
        $select = $this->where("is_actived", 1)
            ->where("is_deleted", 0);
        if ($type == 'and') {
            if (!empty($filters['condition_branch'])) {
                $select->whereIn('branch_id', $filters['condition_branch']);
                unset($filters['condition_branch']);
            }

            if (!empty($filters['condition_department'])) {
                $select->whereIn('department_id', $filters['condition_department']);
                unset($filters['condition_department']);
            }

            if (!empty($filters['condition_titile'])) {
                $select->whereIn('staff_title_id', $filters['condition_titile']);
                unset($filters['condition_titile']);
            }
        } else {

            $condtionBranch =  $filters['condition_branch'] ?? [];
            $condtionDepart = $filters['condition_department'] ?? [];
            $conditionTitile = $filters['condition_titile'] ?? [];
            $select->where(function ($query) use ($condtionBranch, $condtionDepart, $conditionTitile) {
                $query->orWhereIn('department_id', $condtionDepart);
                $query->orWhereIn('branch_id', $condtionBranch);
                $query->orWhereIn('staff_title_id', $conditionTitile);
            });
        }

        return $select->get();
    }
}

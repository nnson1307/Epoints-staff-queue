<?php
/**
 * Created by PhpStorm.
 * User: LE DANG SINH
 * Date: 9/26/2018
 * Time: 4:31 PM
 */

namespace Modules\ManageWork\Models;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use MyCore\Models\Traits\ListTableTrait;

class ManageWorkTable extends Model
{
    use ListTableTrait;
    protected $connection = BRAND_CONNECTION;
    protected $table = 'manage_work';
    protected $primaryKey = 'manage_work_id';

    /**
     * Lấy danh sách lặp lại
     */
    public function getListRepeat(){
        return $this
            ->whereNotNull('repeat_type')
            ->where('repeat_type','<>','none')
            ->get();
    }

    /**
     * Lấy chi tiết công việc
     * @param $manage_work_id
     * @return mixed
     */
    public function getDetailWork($manage_work_id){
        return $this
            ->select(
                'manage_project_id',
                'manage_work_code',
                'manage_type_work_id',
                'manage_work_title',
                'date_start',
                'date_end',
                'processor_id',
                'assignor_id',
                'time',
                'time_type',
                'progress',
                'customer_id',
                'customer_name',
                'description',
                'approve_id',
                'is_approve_id',
                'parent_id',
                'type_card_work',
                'priority',
                'manage_status_id',
                'parent_id',
                'created_by',
                'updated_by'
            )
            ->where('manage_work_id',$manage_work_id)
            ->first();
    }

    /**
     * Kiểm tra code tạo trong ngày
     * @param $code
     */
    public function getCodeWork($code){
        $oSelect = $this
            ->where('manage_work_code','like','%'.$code.'%')
            ->orderBy('manage_work_id','DESC')
            ->first();

        return $oSelect != null ? $oSelect['manage_work_code'] : null;
    }

    /**
     * Tạo công việc
     * @param $data
     * @return mixed
     */
    public function insertWork($data){
        return $this->insertGetId($data);
    }

    /**
     * Lấy danh sách công việc hết hạn
     */
    public function getListOverdue(){
        return $this
            ->join('manage_status_config','manage_status_config.manage_status_id',$this->table.'.manage_status_id')
            ->where($this->table.'.date_end','<',Carbon::now()->format('Y-m-d H:i:00'))
            ->where($this->table.'.is_overdue_noti',0)
//            ->whereNotIn('manage_status_id',[6,7])
            ->whereNotIn('manage_status_config.manage_status_group_config_id',[3,4])
            ->get();
    }

    /**
     * Lấy tổng số công việc trong ngày
     */
    public function getTotalWorkInDay($staff){
        $staffId = $staff['staff_id'];
        $oSelect = $this
            ->select(
                DB::raw('SUM(IF((manage_work.date_start IS NULL OR manage_work.date_start < NOW()) AND manage_work.date_end > NOW() AND manage_work.manage_status_id NOT IN (6,7) , 1 , 0)) as total_work_day'),
                DB::raw('SUM(IF(manage_work.date_end < NOW() AND (manage_work.manage_status_id NOT IN (6,7) ) , 1 , 0)) as total_overdue'),
                DB::raw('SUM(IF(manage_work.date_end < NOW() AND (manage_work.manage_status_id NOT IN (1,6,7) ) , 1 , 0)) as total_start')
            )
            ->leftJoin('staffs','staffs.staff_id',$this->table.'.processor_id')
            ->leftJoin('manage_work_support',function ($sql) use ($staffId){
                $sql->on('manage_work_support.manage_work_id',$this->table.'.manage_work_id')
                    ->where('manage_work_support.staff_id',$staffId);
            });

        if (!isset($data['job_overview'])) {
            $oSelect = $oSelect->where(function ($sql) use($staffId){
                $sql->where($this->table.'.processor_id',$staffId);
            });
        }

        $oSelect = $this->getPermission($oSelect,$staff);

        $oSelect = $oSelect->first();
        return $oSelect;
    }

    public function getPermission($oSelect,$staff){
        $user = $staff;
        $userId = $staff['staff_id'];

        $dataRole = DB::connection(BRAND_CONNECTION)->table('map_role_group_staff')
            ->select('manage_role.role_group_id', 'is_all', 'is_branch', 'is_department', 'is_own')
            ->join('manage_role', 'manage_role.role_group_id', 'map_role_group_staff.role_group_id')
            ->where('staff_id', $userId)
            ->get()->toArray();

        $isAll = $isBranch = $isDepartment = $isOwn = 0;
        foreach ($dataRole as $role){
            $role = (array)$role;
            if($role['is_all']){
                $isAll = 1;
            }

            if($role['is_branch']){
                $isBranch = 1;
            }

            if($role['is_department']){
                $isDepartment = 1;
            }

            if($role['is_own']){
                $isOwn = 1;
            }
        }
        $listManageSupport = DB::connection(BRAND_CONNECTION)->table('manage_work_support')
            ->where('staff_id', $userId)
            ->get()->pluck('manage_work_id')->toArray();
        if($isAll){

        } else if ($isBranch){
            $myBrand = $user['branch_id'];

            $oSelect = $oSelect->join('staffs as per_staff', 'per_staff.staff_id', '=', $this->table.'.processor_id')
                ->where('per_staff.branch_id', $myBrand);
        } else if ($isDepartment){
            $myDep= $user['department_id'];

            $oSelect = $oSelect->join('staffs as per_staff', 'per_staff.staff_id', '=', $this->table.'.processor_id')
                ->where('per_staff.department_id', $myDep);
        } else {
            $listManageSupport = DB::connection(BRAND_CONNECTION)->table('manage_work_support')
                ->where('staff_id', $userId)
                ->get()->pluck('manage_work_id')->toArray();

            $oSelect = $oSelect->where(function ($query) use ($userId, $listManageSupport){
                $query
                    ->where($this->table.'.processor_id', $userId)
                    ->orWhere($this->table.'.assignor_id', $userId)
                    ->orWhere($this->table.'.approve_id', $userId)
                    ->orWhereIn($this->table.'.manage_work_id', $listManageSupport);
            });
        }

        return $oSelect;
    }

    /**
     * Cập nhật công việc
     * @param $data
     * @param $id
     * @return mixed
     */
    public function updateWork($data,$id){
        return $this->where('manage_work_id',$id)->update($data);
    }
}
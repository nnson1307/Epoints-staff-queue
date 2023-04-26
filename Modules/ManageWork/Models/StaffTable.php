<?php
namespace Modules\ManageWork\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class NotificationTable
 * @package Modules\Notification\Models
 * @author DaiDP
 * @since Aug, 2020
 */
class StaffTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = 'staffs';
    protected $primaryKey = 'staff_id';

    protected $fillable = ['staff_id', 'full_name', 'code', 'staff_department_id', 'staff_title_id', 'staff_account_id', 'phone', 'staff_avatar', 'is_active', 'is_delete', 'created_at', 'updated_at', 'created_by', 'updated_by'] ;

    public function getAllStaff(){
        return $this
            ->where('is_actived',1)
            ->where('is_deleted',0)
            ->get();
    }

    public function getListStaffByArrId($arrIdStaff){
        $oSelect = $this
            ->select(
                $this->table.'.staff_id',
                $this->table.'.full_name as staff_name',
                $this->table.'.staff_avatar',
                $this->table.'.email'
            )
            ->where($this->table.'.is_actived',1)
            ->where($this->table.'.is_deleted',0)
            ->whereIn($this->table.'.staff_id',$arrIdStaff);

        return $oSelect->groupBy($this->table.'.staff_id')->get();
    }

    /**
     * lấy nhân viên có quyền admin
     */
    public function getStaffAdmin(){
        return $this
            ->where('is_admin',1)
            ->where('is_actived',1)
            ->where('is_deleted',0)
            ->first();
    }

    public function getAllStaffNotAdmin($staffId){
        return $this
            ->where('staff_id','<>',$staffId)
            ->where($this->table.'.is_actived',1)
            ->where($this->table.'.is_deleted',0)
            ->get();
    }
}

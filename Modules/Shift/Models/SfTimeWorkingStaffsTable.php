<?php
/**
 * Created by PhpStorm.
 * User: nguyenngocson
 * Date: 26/05/2021
 * Time: 14:37
 */

namespace Modules\Shift\Models;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SfTimeWorkingStaffsTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = "sf_time_working_staffs";
    protected $primaryKey = "time_working_staff_id";

    /**
     * Lấy danh sách nhân viên chưa check in , check out theo điều kiện
     */
    public function getListStaff($filter = []){
        $oSelect = $this
            ->join('sf_shifts','sf_shifts.shift_id',$this->table.'.shift_id')
            ->where('sf_shifts.is_actived',1)
            ->where('sf_shifts.is_deleted',0)
            ->where($this->table.'.is_close',0)
            ->where($this->table.'.is_deleted',0);

        if (isset($filter['type_check'])){
            if ($filter['type_check'] == 'checkin'){
                $oSelect = $oSelect
                    ->where($this->table.'.working_day',$filter['date_now'])
                    ->where($this->table.'.working_time',$filter['time_now'])
                    ->where($this->table.'.is_check_in',0);
            } else {
                $oSelect = $oSelect
                    ->where($this->table.'.working_end_day',$filter['date_now'])
                    ->where($this->table.'.working_end_time',$filter['time_now'])
                    ->where($this->table.'.is_check_out',0);
            }
        }

        return $oSelect->get();
    }
}
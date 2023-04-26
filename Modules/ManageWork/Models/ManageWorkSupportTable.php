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
use MyCore\Models\Traits\ListTableTrait;

class ManageWorkSupportTable extends Model
{
    use ListTableTrait;
    protected $connection = BRAND_CONNECTION;
    protected $table = 'manage_work_support';
    protected $primaryKey = 'manage_work_support_id';

    /**
     * Lấy danh sách hỗ trợ theo công việc
     */
    public function getListRepeat($manage_work_id){
        return $this
            ->select('staff_id')
            ->where('manage_work_id',$manage_work_id)
            ->get();
    }

    public function insertSupport($data){
        return $this->insert($data);
    }

    /**
     * lấy danh sách nhân viên liên quan theo công việc
     * @param $manage_work_id
     */
    public function getListStaffByWork($manage_work_id){
        return $this
            ->select(
                'staffs.staff_id',
                'staffs.full_name as staff_name',
                'staffs.email',
                'staffs.staff_avatar'
            )
            ->join('staffs','staffs.staff_id',$this->table.'.staff_id')
            ->where('manage_work_id',$manage_work_id)
            ->get();
    }
}
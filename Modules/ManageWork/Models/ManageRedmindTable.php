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

class ManageRedmindTable extends Model
{
    use ListTableTrait;
    protected $connection = BRAND_CONNECTION;
    protected $table = 'manage_remind';
    protected $primaryKey = 'manage_remind_id';

    /**
     * Lấy danh sách nhắc nhở chưa đọc
     * @return mixed
     */
    public function getAllRemind(){
        return $this
            ->where('date_remind','<=',Carbon::now()->addHours(2))
//            ->whereRaw('IF(manage_remind.time_type = "h" , DATE_SUB(manage_remind.date_remind,INTERVAL 1 HOUR),(IF(manage_remind.time_type = "d",DATE_SUB(manage_remind.date_remind,INTERVAL manage_remind.date_remind DAY),manage_remind.date_remind))) >= DATE_FORMAT(NOW(),"%Y-%m-%d %H:%i:00")')
            ->where('is_sent',0)
            ->get();
    }

    /**
     * Cập nhật nhắc nhở
     */
    public function editRemind($data,$id){
        return $this->where('manage_remind_id',$id)->update($data);
    }

}
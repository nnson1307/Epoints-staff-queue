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

class ManageRepeatTimeTable extends Model
{
    use ListTableTrait;
    protected $connection = BRAND_CONNECTION;
    protected $table = 'manage_repeat_time';
    protected $primaryKey = 'manage_repeat_time_id';

    /**
     * Lấy danh sách ngày lặp
     */
    public function getListRepeatTime($manage_work_id){
        return $this
            ->where('manage_work_id',$manage_work_id)
            ->get();
    }
}
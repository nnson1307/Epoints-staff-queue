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

class SfTimekeepingNotificationTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = "sf_timekeeping_notification";
    protected $primaryKey = "sf_timekeeping_notification_id";

    public function getAll(){
        return $this
            ->where('is_active',1)
            ->where('is_deleted',0)
            ->where('is_noti',1)
            ->get();
    }
}
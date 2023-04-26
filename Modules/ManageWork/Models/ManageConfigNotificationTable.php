<?php
/**
 * Created by PhpStorm.
 * User: nguyenngocson
 * Date: 26/05/2021
 * Time: 14:37
 */

namespace Modules\ManageWork\Models;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ManageConfigNotificationTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = "manage_config_notification";
    protected $primaryKey = "manage_config_notification_id";

    /**
     * Lấy cấu hình theo key
     * @param $key
     */
    public function getConfigByKey($key){
        return $this
            ->select(
                'manage_config_notification_id',
                'manage_config_notification_key',
                'manage_config_notification_title',
                'is_mail',
                'is_noti',
                'manage_config_notification_message',
                'is_created',
                'is_processor',
                'is_support',
                'is_approve',
                'avatar',
                'has_detail',
                'detail_action_name',
                'detail_action',
                'detail_action_params'
            )
            ->where('is_active',1)
            ->where('manage_config_notification_key',$key)
            ->first();
    }
}
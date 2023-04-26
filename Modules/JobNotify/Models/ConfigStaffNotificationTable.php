<?php
/**
 * Created by PhpStorm.
 * User: nguyenngocson
 * Date: 09/04/2021
 * Time: 14:16
 */

namespace Modules\JobNotify\Models;


use Illuminate\Database\Eloquent\Model;

class ConfigStaffNotificationTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = "config_staff_notification";

    const IS_ACTIVE = 1;

    /**
     * Lấy thông tin config notification
     *
     * @param $key
     * @return mixed
     */
    public function getInfo($key)
    {
        return $this
            ->select(
                "{$this->table}.key",
                "{$this->table}.name",
                "{$this->table}.config_staff_notification_group_id",
                "{$this->table}.send_type",
                "{$this->table}.schedule_unit",
                "{$this->table}.value",
                "st_auto.title",
                "st_auto.message",
                "st_auto.avatar",
                "st_auto.has_detail",
                "st_auto.detail_background",
                "st_auto.detail_content",
                "st_auto.detail_action_name",
                "st_auto.detail_action",
                "st_auto.detail_action_params"
            )
            ->join("staff_notification_template_auto as st_auto", "st_auto.key", "=", "{$this->table}.key")
            ->where("{$this->table}.key", $key)
            ->where("{$this->table}.is_active", self::IS_ACTIVE)
            ->first();
    }
}
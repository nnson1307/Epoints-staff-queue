<?php
/**
 * Created by PhpStorm.
 * User: nguyenngocson
 * Date: 09/04/2021
 * Time: 14:16
 */

namespace Modules\JobNotify\Models;


use Illuminate\Database\Eloquent\Model;

class StaffNotificationDetailTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = "staff_notification_detail";
    protected $primaryKey = "staff_notification_detail_id";
    protected $fillable = [
        "staff_notification_detail_id",
        "background",
        "content",
        "action_name",
        "action",
        "action_params",
        "created_by",
        "updated_by"
    ];

    /**
     * Thêm chi tiết thông báo
     *
     * @param array $data
     * @return mixed
     */
    public function add(array $data)
    {
        return $this->create($data)->staff_notification_detail_id;
    }
}
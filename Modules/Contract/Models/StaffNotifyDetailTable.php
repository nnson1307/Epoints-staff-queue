<?php
/**
 * Created by PhpStorm.
 * User: nguyenngocson
 * Date: 25/11/2021
 * Time: 17:01
 */

namespace Modules\Contract\Models;


use Illuminate\Database\Eloquent\Model;

class StaffNotifyDetailTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = 'staff_notification_detail';
    protected $primaryKey = 'staff_notification_detail_id';
    protected $fillable=[
        'tenant_id', 'background', 'content', 'action', 'action_params', 'is_brand', 'action_name',
        'created_at', 'created_by', 'updated_at', 'updated_by', 'staff_notification_detail_id'
    ];

    /**
     * Thêm chi tiết thông báo nhân viên
     *
     * @param array $data
     * @return mixed
     */
    public function add(array $data)
    {
        return $this->create($data)->staff_notification_detail_id;
    }
}
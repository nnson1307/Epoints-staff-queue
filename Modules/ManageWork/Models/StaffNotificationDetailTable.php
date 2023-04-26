<?php
namespace Modules\ManageWork\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class NotificationTable
 * @package Modules\Notification\Models
 * @author DaiDP
 * @since Aug, 2020
 */
class StaffNotificationDetailTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = 'staff_notification_detail';
    protected $primaryKey = 'staff_notification_detail_id';

    protected $fillable = [
        'tenant_id', 'background',
        'content',
        'action_name',
        'action',
        'action_params',
        'is_brand'
    ];

    public function getDetailById($id){
        return $this->where($this->primaryKey, $id)->first()->toArray();
    }

    public function insertNotiDetail($data){
        return $this->insertGetId($data);
    }
}

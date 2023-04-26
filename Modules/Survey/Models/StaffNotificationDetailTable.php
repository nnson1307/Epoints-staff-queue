<?php

namespace Modules\Survey\Models;

use Illuminate\Database\Eloquent\Model;
use MyCore\Models\Traits\ListTableTrait;

class StaffNotificationDetailTable extends Model
{
    use ListTableTrait;
    protected $connection = BRAND_CONNECTION;
    protected $table = 'staff_notification_detail';
    protected $primaryKey = 'staff_notification_detail_id';
    protected $fillable = [
        'tenant_id', 'background', 'content', 'action', 'action_params', 'is_brand', 'action_name',
        'created_at', 'created_by', 'updated_at', 'updated_by', 'staff_notification_detail_id'
    ];

    public function createNotiDetail($data)
    {
        return $this->create($data)->staff_notification_detail_id;
    }
}

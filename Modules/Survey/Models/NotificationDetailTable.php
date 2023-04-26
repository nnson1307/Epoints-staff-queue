<?php

namespace Modules\Survey\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class NotificationTable
 * @package Modules\Notification\Models
 * @author DaiDP
 * @since Aug, 2020
 */
class NotificationDetailTable extends Model
{   
    protected $connection = BRAND_CONNECTION;
    protected $table = 'notification_detail';
    protected $primaryKey = 'notification_detail_id';

    protected $fillable = [
        'tenant_id', 'background',
        'content',
        'action_name',
        'action',
        'action_params',
        'is_brand'
    ];

    public function getDetailById($id)
    {
        return $this->where($this->primaryKey, $id)->first()->toArray();
    }

    
}

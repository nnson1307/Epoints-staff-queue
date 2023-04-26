<?php

namespace Modules\Contract\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ContractStaffQueueTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = 'contract_staff_queue';
    protected $primaryKey = 'contract_staff_queue_id';
    protected $fillable=[
        "contract_staff_queue_id",
        "staff_notification_detail_id",
        "tenant_id",
        "contract_id",
        "staff_id",
        "staff_notification_avatar",
        "staff_notification_title",
        "staff_notification_message",
        "send_at",
        "is_actived",
        "is_send",
        "created_at",
        "created_by",
        "updated_at",
        "updated_by",
    ];

    /**
     * Thêm staff queue
     *
     * @param $data
     * @return mixed
     */
    public function add($data)
    {
        return $this->create($data)->contract_staff_queue_id;
    }

    public function updateSent($data, $id)
    {
        return $this->where("contract_staff_queue_id", $id)->update($data);
    }

    public function getListStaffQueue()
    {
        $ds = $this->select(
            "{$this->table}.contract_staff_queue_id",
            "{$this->table}.staff_notification_detail_id",
            "{$this->table}.tenant_id",
            "{$this->table}.contract_id",
            "{$this->table}.staff_id",
            "{$this->table}.staff_notification_avatar",
            "{$this->table}.staff_notification_title",
            "{$this->table}.staff_notification_message",
            "{$this->table}.send_at",
            "{$this->table}.is_actived",
            "{$this->table}.is_send",
            "staff_notification_detail.content",
            "staff_notification_detail.action_name",
            "staff_notification_detail.action",
            "staff_notification_detail.action_params",
            "staff_notification_detail.is_brand"
        )
            ->join("staff_notification_detail", "staff_notification_detail.staff_notification_detail_id", "{$this->table}.staff_notification_detail_id")
            ->where("{$this->table}.is_actived", 1)
            ->where("{$this->table}.is_send", 0);
        $dateNow = Carbon::now();
        $date5MinutesBefore = Carbon::now()->subMinutes(5);
        $ds->whereBetween("{$this->table}.send_at", [$date5MinutesBefore, $dateNow]);
        return $ds->get();
    }
}

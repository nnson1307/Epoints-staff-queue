<?php
/**
 * Created by PhpStorm
 * User: Mr Son
 * Date: 5/12/2020
 * Time: 5:16 PM
 */

namespace Modules\JobNotify\Models;


use Illuminate\Database\Eloquent\Model;

class DeliveryHistoryTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = "delivery_history";
    protected $primaryKey = "delivery_history_id";

    /**
     * Lấy thông tin phiếu giao hàng
     *
     * @param $deliveryHistoryId
     * @return mixed
     */
    public function getInfo($deliveryHistoryId)
    {
        return $this
            ->select(
                "{$this->table}.delivery_history_id",
                "{$this->table}.delivery_id",
                "{$this->table}.delivery_history_code",
                "{$this->table}.transport_id",
                "{$this->table}.transport_code",
                "{$this->table}.delivery_staff",
                "{$this->table}.delivery_start",
                "{$this->table}.delivery_end",
                "{$this->table}.contact_phone",
                "{$this->table}.contact_name",
                "{$this->table}.contact_address",
                "{$this->table}.amount",
                "{$this->table}.verified_payment",
                "{$this->table}.verified_by",
                "{$this->table}.status",
                "{$this->table}.note",
                "{$this->table}.time_ship",
                "deliveries.order_id"
            )
            ->join("deliveries", "deliveries.delivery_id", "=", "{$this->table}.delivery_id")
            ->where("delivery_history_id", $deliveryHistoryId)
            ->first();
    }
}
<?php
/**
 * Created by PhpStorm
 * User: Mr Son
 * Date: 14-04-02020
 * Time: 2:52 PM
 */

namespace Modules\JobNotify\Models;


use Illuminate\Database\Eloquent\Model;

class OrderTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = "orders";
    protected $primaryKey = "order_id";

    const NOT_DELETE = 0;

    /**
     * lấy thông tin đơn hàng
     *
     * @param $orderId
     * @param $customerId
     * @return mixed
     */
    public function getInfo($orderId, $customerId)
    {
        return $this
            ->select(
                "{$this->table}.order_id",
                "{$this->table}.order_code",
                "{$this->table}.total",
                "{$this->table}.discount",
                "{$this->table}.amount",
                "{$this->table}.process_status",
                "{$this->table}.order_description",
                "{$this->table}.discount_member",
                "{$this->table}.created_at",
                "{$this->table}.customer_id",
                "customers.full_name as customer_name"
            )
            ->join("customers", "customers.customer_id", "=", "{$this->table}.customer_id")
            ->where("{$this->table}.order_id", $orderId)
            ->where("{$this->table}.customer_id", $customerId)
            ->where("{$this->table}.is_deleted", self::NOT_DELETE)
            ->first();
    }
}
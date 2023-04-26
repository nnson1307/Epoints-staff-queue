<?php

namespace Modules\Commission\Models;

use Illuminate\Database\Eloquent\Model;

class ContractMapOrderTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = "contract_map_order";
    protected $primaryKey = "contract_map_order_id";

    const PAY_SUCCESS = "paysuccess";
    const PAY_HALF = "pay-half";

    /**
     * Lấy đơn hàng gần nhất map với hợp đồng
     *
     * @param $contractCode
     * @return mixed
     */
    public function getOrderByContract($contractCode)
    {
        return $this
            ->select(
                "{$this->table}.contract_code",
                "o.order_id",
                "o.order_code",
                "o.process_status",
                "o.total",
                "o.discount",
                "o.amount",
                "o.tranport_charge",
            )
            ->join("orders as o", "o.order_code", "=", "{$this->table}.order_code")
            ->where("{$this->table}.contract_code", $contractCode)
            ->whereIn("o.process_status", [self::PAY_SUCCESS, self::PAY_HALF])
            ->groupBy("{$this->table}.contract_code")
            ->orderBy("{$this->table}.contract_map_order_id", "desc")
            ->first();
    }
}
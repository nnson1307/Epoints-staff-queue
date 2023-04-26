<?php
/**
 * Created by PhpStorm.
 * User: nguyenngocson
 * Date: 18/10/2021
 * Time: 15:23
 */

namespace Modules\JobNotify\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrderDetailTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = "order_details";
    protected $primaryKey = "order_detail_id";

    /**
     * Đếm số lượng sản phẩm/dv theo đơn hàng
     *
     * @param $orderId
     * @return mixed
     */
    public function sumTotalProduct($orderId)
    {
        return $this
            ->select(
                DB::raw("SUM(quantity) as total_quantity")
            )
            ->where("order_id", $orderId)
            ->groupBy("order_id")
            ->first();
    }
}
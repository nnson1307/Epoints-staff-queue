<?php
/**
 * Created by PhpStorm
 * User: Mr Son
 * Date: 15-04-02020
 * Time: 10:24 AM
 */

namespace Modules\JobNotify\Models;


use Illuminate\Database\Eloquent\Model;

class CustomerTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = "customers";
    protected $primaryKey = "customer_id";

    /**
     * Lấy thông tin khách hàng
     *
     * @param $customerId
     * @return mixed
     */
    public function getInfo($customerId)
    {
        return $this
            ->select(
                "customer_id",
                "customer_code",
                "full_name",
                "birthday",
                "gender",
                "phone1",
                "address",
                "customer_avatar",
                "created_at"
            )
            ->where("$this->primaryKey", $customerId)
            ->first();
    }
}
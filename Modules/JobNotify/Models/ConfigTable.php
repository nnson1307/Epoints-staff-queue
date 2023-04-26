<?php
/**
 * Created by PhpStorm.
 * User: nguyenngocson
 * Date: 28/10/2021
 * Time: 09:35
 */

namespace Modules\JobNotify\Models;


use Illuminate\Database\Eloquent\Model;

class ConfigTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = "config";
    protected $primaryKey = "config_id";

    const keyBranchOrder = "branch_apply_order";

    /**
     * Lấy thông tin cấu hình
     *
     * @param $key
     * @return mixed
     */
    public function getConfig($key)
    {
        return $this
            ->select(
                "config_id",
                "name",
                "key",
                "value"
            )
            ->where("key", $key)
            ->first();
    }

    /**
     * Lấy chi nhánh mặc định khi đơn hàng ko có chi nhánh
     *
     * @return mixed
     */
    public function getBranchApplyOrder()
    {
        return $this
            ->select(
                "branches.branch_id",
                "branches.branch_code"
            )
            ->join("branches", "branches.branch_id", "=", "{$this->table}.value")
            ->where("{$this->table}.key", self::keyBranchOrder)
            ->first();
    }
}
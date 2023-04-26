<?php
/**
 * Created by PhpStorm.
 * User: nguyenngocson
 * Date: 25/11/2021
 * Time: 17:12
 */

namespace Modules\Contract\Models;


use Illuminate\Database\Eloquent\Model;

class StaffTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = "staffs";
    protected $primaryKey = "staff_id";

    const IS_ACTIVE = 1;
    const NOT_DELETED = 0;

    /**
     * Lấy thông tin nhân viên
     *
     * @param $staffId
     * @return mixed
     */
    public function getInfo($staffId)
    {
        return $this
            ->select(
                "staff_id",
                "full_name",
                "email",
                "phone1 as phone"
            )
            ->where("is_actived", self::IS_ACTIVE)
            ->where("is_deleted", self::NOT_DELETED)
            ->where("staff_id", $staffId)
            ->first();
    }
}
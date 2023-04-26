<?php
/**
 * Created by PhpStorm.
 * User: nguyenngocson
 * Date: 15/04/2021
 * Time: 10:28
 */

namespace Modules\JobNotify\Models;


use Illuminate\Database\Eloquent\Model;

class StaffTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = "staffs";
    protected $primaryKey = "staff_id";

    const IS_ACTIVE = 1;
    const NOT_DELETED = 0;

    /**
     * Lấy ds nhân viên
     *
     * @return mixed
     */
    public function getStaff()
    {
        return $this
            ->select(
                "staff_id",
                "branch_id",
                "full_name"
            )
            ->where("is_actived", self::IS_ACTIVE)
            ->where("is_deleted", self::NOT_DELETED)
            ->get();
    }
}
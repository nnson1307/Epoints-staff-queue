<?php
/**
 * Created by PhpStorm.
 * User: nguyenngocson
 * Date: 03/08/2022
 * Time: 10:58
 */

namespace Modules\JobNotify\Models;


use Illuminate\Database\Eloquent\Model;

class MapRoleGroupStaffTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = "map_role_group_staff";
    protected $primaryKey = "id";

    const IS_ACTIVE = 1;
    const NOT_DELETED = 0;

    /**
     * Lấy nhân viên theo role và chi nhánh
     *
     * @param $arrayRoleGroup
     * @param $branchId
     * @return mixed
     */
    public function getStaffByArrayRole($arrayRoleGroup, $branchId)
    {
        return $this
            ->select(
                "s.staff_id",
                "s.branch_id",
                "s.full_name"
            )
            ->join("staffs as s", "s.staff_id", "=", "{$this->table}.staff_id")
            ->whereIn("{$this->table}.role_group_id", $arrayRoleGroup)
            ->where("s.is_actived", self::IS_ACTIVE)
            ->where("s.is_deleted", self::NOT_DELETED)
            ->where("s.branch_id", $branchId)
            ->groupBy("s.staff_id")
            ->get();
    }
}
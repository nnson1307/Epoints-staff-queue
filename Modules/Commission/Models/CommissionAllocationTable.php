<?php

namespace Modules\Commission\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionAllocationTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = "commission_allocation";
    protected $primaryKey = "commission_allocation_id";
    protected $fillable = [
        'commission_allocation_id',
        'staff_id',
        'commission_id',
        'commission_coefficient'
    ];

    const IS_ACTIVE = 1;
    const NOT_DELETED = 0;

    /**
     * Lấy hoa hồng được phân bổ cho nhân viên
     *
     * @return mixed
     */
    public function getStaffAllocation()
    {
        return $this
            ->select(
                "{$this->table}.commission_allocation_id",
                "{$this->table}.staff_id",
                "{$this->table}.commission_id",
                "{$this->table}.commission_coefficient",
                "s.full_name as staff_name",
                "c.commission_name",
                "c.start_effect_time",
                "c.end_effect_time",
                "c.commission_type",
                "c.apply_time",
                "c.calc_apply_time",
                "c.commission_calc_by",
                "c.commission_scope",
                "c.order_commission_type",
                "c.order_commission_group_type",
                "c.order_commission_object_type",
                "c.order_commission_calc_by",
                "c.kpi_commission_calc_by",
                "c.contract_commission_calc_by",
                "c.contract_commission_type",
                "c.contract_commission_condition",
                "c.contract_commission_time",
                "c.contract_commission_operation",
                "c.contract_commission_apply",
                "s.team_id",
                "s.branch_id",
                "s.department_id"
            )
            ->join("staffs as s", "s.staff_id", "=", "{$this->table}.staff_id")
            ->join("commission as c", "c.commission_id", "=", "{$this->table}.commission_id")
            ->where("s.is_actived", self::IS_ACTIVE)
            ->where("s.is_deleted", self::NOT_DELETED)
            ->where("c.status", self::IS_ACTIVE)
            ->where("c.is_deleted", self::NOT_DELETED)
            ->orderBy("{$this->table}.commission_allocation_id", "asc")
            ->get();
    }
}
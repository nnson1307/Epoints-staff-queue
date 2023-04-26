<?php

namespace Modules\Commission\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionConfigTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = "commission_config";
    protected $primaryKey = "commission_config_id";

    protected $casts = [
        'min_value' => 'float',
        'max_value' => 'float',
        'commission_value' => 'float',
    ];

    const NOT_DELETED = 0;

    /**
     * Lấy cấu hình điều kiện của hoa hồng theo mức
     *
     * @param $idCommission
     * @param $valueNumber
     * @return mixed
     */
    public function getConfigByLevel($idCommission, $valueNumber)
    {
        return $this
            ->select(
                "{$this->table}.commission_config_id",
                "{$this->table}.commission_id",
                "{$this->table}.min_value",
                "{$this->table}.max_value",
                "{$this->table}.commission_value",
                "{$this->table}.config_operation"
            )
            ->where("{$this->table}.commission_id", $idCommission)
            ->where("{$this->table}.min_value", "<=", $valueNumber)
            ->where(function ($query) use ($valueNumber) {
                $query->whereNull("{$this->table}.max_value")
                    ->orWhere("{$this->table}.max_value", ">", $valueNumber);
            })
            ->where("{$this->table}.is_deleted", self::NOT_DELETED)
            ->first();
    }

    /**
     * Lấy cấu hình điều kiện của hoa hồng theo bậc thang
     *
     * @param $idCommission
     * @param $valueNumber
     * @return mixed
     */
    public function getConfigByStep($idCommission, $valueNumber)
    {
        return $this
            ->select(
                "{$this->table}.commission_config_id",
                "{$this->table}.commission_id",
                "{$this->table}.min_value",
                "{$this->table}.max_value",
                "{$this->table}.commission_value",
                "{$this->table}.config_operation"
            )
            ->where("{$this->table}.commission_id", $idCommission)
            ->where("{$this->table}.min_value", "<=", $valueNumber)
            ->where(function ($query) use ($valueNumber) {
                $query->whereNull("{$this->table}.max_value")
                    ->orWhere("{$this->table}.min_value", "<=", $valueNumber);
            })
            ->where("{$this->table}.is_deleted", self::NOT_DELETED)
            ->get();
    }
}
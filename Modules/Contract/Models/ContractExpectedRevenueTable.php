<?php
/**
 * Created by PhpStorm.
 * User: nguyenngocson
 * Date: 24/11/2021
 * Time: 16:33
 */

namespace Modules\Contract\Models;


use Illuminate\Database\Eloquent\Model;

class ContractExpectedRevenueTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = "contract_expected_revenue";
    protected $primaryKey = "contract_expected_revenue_id";

    const NOT_DELETED = 0;
    const CANCEL = "cancel";
    const IS_ACTIVE = 1;

    /**
     * Lấy dự kiến thu - chi của HĐ
     *
     * @return mixed
     */
    public function getExpectedRevenue()
    {
        return $this
            ->select(
                "{$this->table}.contract_expected_revenue_id",
                "{$this->table}.contract_id",
                "{$this->table}.contract_category_remind_id",
                "{$this->table}.type",
                "{$this->table}.send_type",
                "{$this->table}.send_value",
                "{$this->table}.send_value_child",
                "remind.title",
                "remind.content",
                "remind.recipe",
                "remind.unit",
                "unit_value",
                "compare_unit"
            )
            ->join("contracts as ct", "ct.contract_id", "=", "{$this->table}.contract_id")
            ->join("contract_category_status as ct_status", "ct_status.status_code", "=", "ct.status_code")
            ->join("contract_category_remind as remind", "remind.contract_category_remind_id", "=", "{$this->table}.contract_category_remind_id")
            ->where("{$this->table}.is_deleted", self::NOT_DELETED)
            ->where("ct.is_deleted", self::NOT_DELETED)
            ->where("ct_status.default_system", "<>", self::CANCEL)
            ->where("remind.is_actived", self::IS_ACTIVE)
            ->where("remind.is_deleted", self::NOT_DELETED)
            ->get();
    }
}
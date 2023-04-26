<?php
/**
 * Created by PhpStorm.
 * User: nguyenngocson
 * Date: 26/11/2021
 * Time: 10:08
 */

namespace Modules\Contract\Models;


use Illuminate\Database\Eloquent\Model;

class ContractCategoryRemindTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = "contract_category_remind";
    protected $primaryKey = "contract_category_remind_id";

    const IS_ACTIVE = 1;
    const NOT_DELETED = 0;

    const RECEIPT_DUE = "receive_due_date";
    const SPEND_DUE = "spend_due_date";
    const CONTRACT_DUE_SOON = "contract_due_soon";
    const CONTRACT_DUE_DATE = "contract_due_date";
    const WARRANTY_DUE_DATE = "warranty_due_date";
    const WARRANTY_DUE_SOON = "warranty_due_soon";

    /**
     * Lấy cấu hình nhắc nhở đến hạn thu - chi
     *
     * @return mixed
     */
    public function getRemindDue()
    {
        return $this
            ->select(
                "{$this->table}.contract_category_remind_id",
                "{$this->table}.remind_type",
                "{$this->table}.title",
                "{$this->table}.content",
                "{$this->table}.recipe",
                "{$this->table}.unit",
                "{$this->table}.unit_value",
                "{$this->table}.compare_unit",
                "ct.contract_category_name",
                "{$this->table}.contract_category_id"
            )
            ->join("contract_categories as ct", "ct.contract_category_id", "=", "{$this->table}.contract_category_id")
            ->where("{$this->table}.is_actived", self::IS_ACTIVE)
            ->where("{$this->table}.is_deleted", self::NOT_DELETED)
            ->where("ct.is_actived", self::IS_ACTIVE)
            ->where("ct.is_deleted", self::NOT_DELETED)
            ->whereIn("{$this->table}.remind_type", [self::RECEIPT_DUE, self::SPEND_DUE])
            ->get();
    }

    /**
     * Lấy cấu hình nhắc nhở sắp đến hạn HĐ
     *
     * @return mixed
     */
    public function getRemindComingEnd()
    {
        return $this
            ->select(
                "{$this->table}.contract_category_remind_id",
                "{$this->table}.remind_type",
                "{$this->table}.title",
                "{$this->table}.content",
                "{$this->table}.recipe",
                "{$this->table}.unit",
                "{$this->table}.unit_value",
                "{$this->table}.compare_unit",
                "ct.contract_category_name",
                "{$this->table}.contract_category_id"
            )
            ->join("contract_categories as ct", "ct.contract_category_id", "=", "{$this->table}.contract_category_id")
            ->where("{$this->table}.is_actived", self::IS_ACTIVE)
            ->where("{$this->table}.is_deleted", self::NOT_DELETED)
            ->where("ct.is_actived", self::IS_ACTIVE)
            ->where("ct.is_deleted", self::NOT_DELETED)
            ->whereIn("{$this->table}.remind_type", [self::CONTRACT_DUE_SOON])
            ->get();
    }

    /**
     * Lấy cấu hình nhắc nhở đến hạn HĐ
     *
     * @return mixed
     */
    public function getRemindDueDate()
    {
        return $this
            ->select(
                "{$this->table}.contract_category_remind_id",
                "{$this->table}.remind_type",
                "{$this->table}.title",
                "{$this->table}.content",
                "{$this->table}.recipe",
                "{$this->table}.unit",
                "{$this->table}.unit_value",
                "{$this->table}.compare_unit",
                "ct.contract_category_name",
                "{$this->table}.contract_category_id"
            )
            ->join("contract_categories as ct", "ct.contract_category_id", "=", "{$this->table}.contract_category_id")
            ->where("{$this->table}.is_actived", self::IS_ACTIVE)
            ->where("{$this->table}.is_deleted", self::NOT_DELETED)
            ->where("ct.is_actived", self::IS_ACTIVE)
            ->where("ct.is_deleted", self::NOT_DELETED)
            ->whereIn("{$this->table}.remind_type", [self::CONTRACT_DUE_DATE])
            ->get();
    }

    /**
     * Lấy cấu hình nhắc nhở đến hạn bảo hành
     *
     * @return mixed
     */
    public function getRemindWarrantyDueDate()
    {
        return $this
            ->select(
                "{$this->table}.contract_category_remind_id",
                "{$this->table}.remind_type",
                "{$this->table}.title",
                "{$this->table}.content",
                "{$this->table}.recipe",
                "{$this->table}.unit",
                "{$this->table}.unit_value",
                "{$this->table}.compare_unit",
                "ct.contract_category_name",
                "{$this->table}.contract_category_id"
            )
            ->join("contract_categories as ct", "ct.contract_category_id", "=", "{$this->table}.contract_category_id")
            ->where("{$this->table}.is_actived", self::IS_ACTIVE)
            ->where("{$this->table}.is_deleted", self::NOT_DELETED)
            ->where("ct.is_actived", self::IS_ACTIVE)
            ->where("ct.is_deleted", self::NOT_DELETED)
            ->whereIn("{$this->table}.remind_type", [self::WARRANTY_DUE_DATE])
            ->get();
    }

    /**
     * Lấy cấu hình nhắc nhở sắp đến hạn bảo hành
     *
     * @return mixed
     */
    public function getRemindWarrantyComingEnd()
    {
        return $this
            ->select(
                "{$this->table}.contract_category_remind_id",
                "{$this->table}.remind_type",
                "{$this->table}.title",
                "{$this->table}.content",
                "{$this->table}.recipe",
                "{$this->table}.unit",
                "{$this->table}.unit_value",
                "{$this->table}.compare_unit",
                "ct.contract_category_name",
                "{$this->table}.contract_category_id"
            )
            ->join("contract_categories as ct", "ct.contract_category_id", "=", "{$this->table}.contract_category_id")
            ->where("{$this->table}.is_actived", self::IS_ACTIVE)
            ->where("{$this->table}.is_deleted", self::NOT_DELETED)
            ->where("ct.is_actived", self::IS_ACTIVE)
            ->where("ct.is_deleted", self::NOT_DELETED)
            ->whereIn("{$this->table}.remind_type", [self::WARRANTY_DUE_SOON])
            ->get();
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: nguyenngocson
 * Date: 23/08/2021
 * Time: 14:37
 */

namespace Modules\Contract\Models;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use MyCore\Models\Traits\ListTableTrait;

class ContractTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = "contracts";
    protected $primaryKey = "contract_id";
    protected $fillable = [
        "contract_id",
        "contract_category_id",
        "contract_name",
        "contract_code",
        "contract_no",
        "sign_date",
        "performer_by",
        "effective_date",
        "expired_date",
        "warranty_start_date",
        "warranty_end_date",
        "content",
        "note",
        "status_code",
        "is_value_goods",
        "is_renew",
        "number_day_renew",
        "is_created_ticket",
        "status_code_created_ticket",
        "ticket_code",
        "reason_remove",
        "is_deleted",
        "custom_1",
        "custom_2",
        "custom_3",
        "custom_4",
        "custom_5",
        "custom_6",
        "custom_7",
        "custom_8",
        "custom_9",
        "custom_10",
        "custom_11",
        "custom_12",
        "custom_13",
        "custom_14",
        "custom_15",
        "custom_16",
        "custom_17",
        "custom_18",
        "custom_19",
        "custom_20",
        "created_by",
        "updated_by",
        "created_at",
        "updated_at"
    ];

    const NOT_DELETED = 0;
    const CANCEL = "cancel";

    /**
     * get all contract expire this day running
     *
     * @return mixed
     */
    public function getContractExpire()
    {
        $dateNow = Carbon::now()->subDays(1)->format('Y-m-d');
        $ds = $this->select("contracts.contract_id")
            ->leftJoin("contract_categories", "contract_categories.contract_category_id", "contracts.contract_category_id")
            ->leftJoin("contract_category_status", function($join){
                $join->on("contract_category_status.status_code", "=", "{$this->table}.status_code")
                    ->on("contract_category_status.contract_category_id", "=", "contract_categories.contract_category_id");
            })
            ->where("contracts.is_deleted", 0)
            ->where("contract_categories.type", "sell")
            ->where(function($join)use($dateNow){
                $join->where(function($subJoin1)use($dateNow){
                    $subJoin1
                        ->where("contracts.is_renew", 1)
                        ->whereNotNull("contracts.expired_date")
                        ->whereBetween("contracts.expired_date", [$dateNow . " 00:00:00", $dateNow . " 23:59:59"]);
                })
                    ->orWhere(function($subJoin2)use($dateNow){
                        $subJoin2
                            ->where("contract_category_status.default_system", "liquidated")
                            ->whereBetween("contracts.updated_at", [$dateNow . " 00:00:00", $dateNow . " 23:59:59"]);
                    });
            });
        return $ds->get();
    }

    /**
     * get all contract soon expire this day running
     *
     * @return mixed
     */
    public function getContractSoonExpire()
    {
        $dateNow = Carbon::now()->format('Y-m-d');
        $ds = $this->select("contracts.contract_id")
            ->leftJoin("contract_categories", "contract_categories.contract_category_id", "contracts.contract_category_id")
            ->leftJoin("contract_category_status", function($join){
                $join->on("contract_category_status.status_code", "=", "{$this->table}.status_code")
                    ->on("contract_category_status.contract_category_id", "=", "contract_categories.contract_category_id");
            })
            ->where("contracts.is_deleted", 0)
            ->where("contract_categories.type", "sell")
            ->where("contracts.is_renew", 1)
            ->whereNotNull("contracts.expired_date")
            ->whereBetween(DB::raw("DATE_SUB(contracts.expired_date, INTERVAL contracts.number_day_renew DAY)"),
                [$dateNow . " 00:00:00", $dateNow . " 23:59:59"]);
        return $ds->get();
    }

    /**
     * Lấy thông tin HĐ
     *
     * @param $contractId
     * @return mixed
     */
    public function getInfo($contractId)
    {
        return $this
            ->select(
                "{$this->table}.contract_id",
                "{$this->table}.contract_category_id",
                "{$this->table}.contract_name",
                "{$this->table}.contract_code",
                "{$this->table}.contract_no",
                "{$this->table}.sign_date",
                "{$this->table}.performer_by",
                "{$this->table}.effective_date",
                "{$this->table}.expired_date",
                "{$this->table}.warranty_start_date",
                "{$this->table}.warranty_end_date",
                "{$this->table}.content",
                "{$this->table}.note",
                "{$this->table}.status_code",
                "{$this->table}.is_value_goods",
                "{$this->table}.is_renew",
                "{$this->table}.number_day_renew",
                "{$this->table}.is_created_ticket",
                "{$this->table}.status_code_created_ticket",
                "{$this->table}.ticket_code",
                "{$this->table}.is_deleted",
                "{$this->table}.custom_1",
                "{$this->table}.custom_2",
                "{$this->table}.custom_3",
                "{$this->table}.custom_4",
                "{$this->table}.custom_5",
                "{$this->table}.custom_6",
                "{$this->table}.custom_7",
                "{$this->table}.custom_8",
                "{$this->table}.custom_9",
                "{$this->table}.custom_10",
                "{$this->table}.custom_11",
                "{$this->table}.custom_12",
                "{$this->table}.custom_13",
                "{$this->table}.custom_14",
                "{$this->table}.custom_15",
                "{$this->table}.custom_16",
                "{$this->table}.custom_17",
                "{$this->table}.custom_18",
                "{$this->table}.custom_19",
                "{$this->table}.custom_20",
                "{$this->table}.created_by",
                "{$this->table}.updated_by",
                "{$this->table}.created_at",
                "{$this->table}.updated_at",
                "ctc.contract_category_name",
                "ctc.type",
                "st.status_name",
                "{$this->table}.is_browse",
                "sf.full_name as performer_name"
            )
            ->join("contract_categories as ctc", "ctc.contract_category_id", "=", "{$this->table}.contract_category_id")
            ->join("contract_category_status as st", "st.status_code", "=", "{$this->table}.status_code")
            ->leftJoin("staffs as sf", "sf.staff_id", "=", "{$this->table}.performer_by")
            ->where("{$this->table}.contract_id", $contractId)
            ->where("{$this->table}.is_deleted", self::NOT_DELETED)
            ->where("st.default_system", "<>", self::CANCEL)
            ->first();
    }

    /**
     * Lấy list HĐ bằng loại HĐ
     *
     * @param $categoryId
     * @return mixed
     */
    public function getContractByCategory($categoryId)
    {
        return $this
            ->select(
                "{$this->table}.contract_id",
                "{$this->table}.contract_category_id",
                "{$this->table}.contract_name",
                "{$this->table}.contract_code",
                "{$this->table}.contract_no",
                "{$this->table}.sign_date",
                "{$this->table}.performer_by",
                "{$this->table}.effective_date",
                "{$this->table}.expired_date",
                "{$this->table}.warranty_start_date",
                "{$this->table}.warranty_end_date",
                "{$this->table}.content",
                "{$this->table}.note",
                "{$this->table}.status_code",
                "{$this->table}.is_value_goods",
                "{$this->table}.is_renew",
                "{$this->table}.number_day_renew",
                "{$this->table}.is_created_ticket",
                "{$this->table}.status_code_created_ticket",
                "{$this->table}.ticket_code",
                "{$this->table}.is_deleted",
                "{$this->table}.custom_1",
                "{$this->table}.custom_2",
                "{$this->table}.custom_3",
                "{$this->table}.custom_4",
                "{$this->table}.custom_5",
                "{$this->table}.custom_6",
                "{$this->table}.custom_7",
                "{$this->table}.custom_8",
                "{$this->table}.custom_9",
                "{$this->table}.custom_10",
                "{$this->table}.custom_11",
                "{$this->table}.custom_12",
                "{$this->table}.custom_13",
                "{$this->table}.custom_14",
                "{$this->table}.custom_15",
                "{$this->table}.custom_16",
                "{$this->table}.custom_17",
                "{$this->table}.custom_18",
                "{$this->table}.custom_19",
                "{$this->table}.custom_20",
                "{$this->table}.created_by",
                "{$this->table}.updated_by",
                "{$this->table}.created_at",
                "{$this->table}.updated_at",
                "ctc.contract_category_name",
                "ctc.type",
                "st.status_name",
                "{$this->table}.is_browse",
                "sf.full_name as performer_name"
            )
            ->join("contract_categories as ctc", "ctc.contract_category_id", "=", "{$this->table}.contract_category_id")
            ->join("contract_category_status as st", "st.status_code", "=", "{$this->table}.status_code")
            ->leftJoin("staffs as sf", "sf.staff_id", "=", "{$this->table}.performer_by")
            ->where("{$this->table}.contract_category_id", $categoryId)
            ->where("{$this->table}.is_deleted", self::NOT_DELETED)
            ->where("st.default_system", "<>", self::CANCEL)
            ->get();
    }
}
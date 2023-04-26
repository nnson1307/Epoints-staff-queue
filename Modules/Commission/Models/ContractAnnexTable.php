<?php

namespace Modules\Commission\Models;

use Illuminate\Database\Eloquent\Model;

class ContractAnnexTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = "contract_annex";
    protected $primaryKey = "contract_annex_id";

    const RENEW_CONTRACT = "renew_contract";
    const IS_ACTIVE = 1;
    const NOT_DELETED = 0;
    const PROCESSING = "processing";

    public function getRenewContractInDate($date, $scope, $scopeObjectId, $idCategory, $partnerObjectForm)
    {
        $ds = $this
            ->select(
                "c.contract_id",
                "c.contract_code",
                "cat.contract_category_name",
                "c.effective_date",
                "c.expired_date"
            )
            ->join("contracts as c", "c.contract_id", "=", "{$this->table}.contract_id")
            ->join("contract_categories as cat", "cat.contract_category_id", "=", "c.contract_category_id")
            ->join("contract_partner as p", "p.contract_id", "=", "c.contract_id")
            ->join("staffs as s", "s.staff_id", "=", "c.performer_by")
            ->join("contract_category_status as st", "st.status_code", "=", "c.status_code")
//            ->where("{$this->table}.sign_date", $date)
            ->where("c.is_deleted", self::NOT_DELETED)
            ->where("{$this->table}.is_active", self::IS_ACTIVE)
            ->where("{$this->table}.is_deleted", self::NOT_DELETED)
            ->where("{$this->table}.adjustment_type", self::RENEW_CONTRACT);

        //Lấy theo giá trị của (cá nhân/ nhóm)
        switch ($scope) {
            case 'personal':
                $ds->where("c.performer_by", $scopeObjectId);
                break;
            case 'group';
                $ds->where("s.team_id", $scopeObjectId);
                break;
        }

        //Lấy giá trị theo loại HĐ
        if ($idCategory != null) {
            $ds->where("c.contract_category_id", $idCategory);
        }

        //Lấy giá trị theo hình thức đối tác
        if ($partnerObjectForm != null && $partnerObjectForm != 'all') {
            $ds->where("p.partner_object_form", $partnerObjectForm);
        }

        return $ds->get();
    }
}
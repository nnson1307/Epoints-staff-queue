<?php

namespace Modules\Commission\Models;

use Illuminate\Database\Eloquent\Model;

class ContractTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = "contracts";
    protected $primaryKey = "contract_id";

    const NOT_DELETED = 0;
    const PROCESSING = "processing";


    /**
     * Lấy hợp đồng có hiệu lực trong ngày
     *
     * @param $date
     * @param $scope
     * @param $scopeObjectId
     * @param $idCategory
     * @param $contractForm
     * @param $partnerObjectForm
     * @return mixed
     */
    public function getContractInDate($date, $scope, $scopeObjectId, $idCategory, $contractForm, $partnerObjectForm)
    {
        $ds = $this
            ->select(
                "{$this->table}.contract_id",
                "{$this->table}.contract_code",
                "cat.contract_category_name",
                "{$this->table}.effective_date",
                "{$this->table}.expired_date"
            )
            ->join("contract_categories as cat", "cat.contract_category_id", "=", "{$this->table}.contract_category_id")
            ->join("contract_partner as p", "p.contract_id", "=", "{$this->table}.contract_id")
            ->join("staffs as s", "s.staff_id", "=", "{$this->table}.performer_by")
            ->join("contract_category_status as st", "st.status_code", "=", "{$this->table}.status_code")
//            ->where("{$this->table}.effective_date", $date)
            ->where("{$this->table}.is_deleted", self::NOT_DELETED)
            ->where("st.default_system", self::PROCESSING);

        //Lấy theo giá trị của (cá nhân/ nhóm)
        switch ($scope) {
            case 'personal':
                $ds->where("{$this->table}.performer_by", $scopeObjectId);
                break;
            case 'group';
                $ds->where("s.team_id", $scopeObjectId);
                break;
        }

        //Lấy giá trị theo loại HĐ
        if ($idCategory != null) {
            $ds->where("{$this->table}.contract_category_id", $idCategory);
        }

        //Lấy giá trị theo hình thức hợp đồng
        if ($contractForm != null && $contractForm != 'all') {
            $ds->where("{$this->table}.contract_form", $contractForm);
        }

        //Lấy giá trị theo hình thức đối tác
        if ($partnerObjectForm != null && $partnerObjectForm != 'all') {
            $ds->where("p.partner_object_form", $partnerObjectForm);
        }

        return $ds->get();
    }

    /**
     * Chỉnh sửa hợp đồng
     *
     * @param array $data
     * @param $contractId
     * @return mixed
     */
    public function edit(array $data, $contractId)
    {
        return $this->where("{$this->table}.contract_id", $contractId)->update($data);
    }
}
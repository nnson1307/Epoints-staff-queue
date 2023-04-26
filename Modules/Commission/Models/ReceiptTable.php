<?php

namespace Modules\Commission\Models;

use Illuminate\Database\Eloquent\Model;

class ReceiptTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = "receipts";
    protected $primaryKey = "receipt_id";

    const NOT_DELETED = 0;
    const PAY_SUCCESS = "paysuccess";
    const PAY_HALF = "pay-half";
    const PROCESSING = "processing";
    const RENEW_CONTRACT = "renew_contract";
    const IS_ACTIVE = 1;

    /**
     * Lấy phiếu thu của đơn hàng
     *
     * @param $idOrder
     * @return mixed
     */
    public function getReceiptByOrder($idOrder)
    {
        return $this
            ->select(
                "{$this->table}.receipt_id",
                "{$this->table}.receipt_code",
                "{$this->table}.amount_paid"
            )
            ->where("{$this->table}.order_id", $idOrder)
            ->where("{$this->table}.is_deleted", self::NOT_DELETED)
            ->get();
    }

    /**
     * Lấy phiếu thu trong ngày
     *
     * @param $date
     * @return mixed
     */
    public function getReceiptByDate($date, $scope, $scopeObjectId, $objectType, $arrObjectId)
    {
        $ds = $this
            ->select(
                "{$this->table}.receipt_id",
                "{$this->table}.receipt_code",
                "{$this->table}.amount_paid",
                "{$this->table}.order_id"
            )
            ->join("orders as o", "o.order_id", "=", "{$this->table}.order_id")
            ->join("order_details as d", "d.order_id", "=", "{$this->table}.order_id")
            ->join("staffs as s", "s.staff_id", "=", "o.created_by")

            ->leftJoin("product_childs as child", "child.product_child_id", "=", "d.object_id")
            ->leftJoin("products as p", "p.product_id", "=", "child.product_id")
            ->leftJoin("product_categories as pc", "pc.product_category_id", "=", "p.product_category_id")

            ->leftJoin("services as sv", "sv.service_id", "=", "d.object_id")
            ->leftJoin("service_categories as sv_cate", "sv_cate.service_category_id", "=", "sv.service_category_id")

            ->leftJoin("service_cards as svc", "svc.service_card_id", "=", "d.object_id")
            ->leftJoin("service_card_groups as svc_group", "svc_group.service_card_group_id", "=", "svc.service_card_group_id")

            ->whereIn("o.process_status", [self::PAY_SUCCESS, self::PAY_HALF])
            ->whereDate("{$this->table}.created_at", $date)
            ->where("{$this->table}.is_deleted", self::NOT_DELETED)
            ->groupBy("{$this->table}.order_id");

        //Lấy theo giá trị của (cá nhân/ nhóm)
        switch ($scope) {
            case 'personal':
                $ds->where("o.created_by", $scopeObjectId);
                break;
            case 'group';
                $ds->where("s.team_id", $scopeObjectId);
                break;
        }

        //Áp dụng cho loại hàng hoá
        if ($objectType != null && $objectType != 'all') {
            $ds->where("d.object_type", $objectType);
        }

        //Áp dụng đơn hàng có hàng hoá
        if (count($arrObjectId) > 0) {
            $ds->whereIn("d.object_id", $arrObjectId);
        }

        return $ds->get();
    }

    /**
     * Lấy phiếu thu hợp đồng (mới, tái kí)
     *
     * @param $date
     * @param $scope
     * @param $scopeObjectId
     * @param $idCategory
     * @param $contractForm
     * @param $partnerObjectForm
     * @return mixed
     */
    public function getReceiptContractByDate($date, $scope, $scopeObjectId, $idCategory, $contractForm, $partnerObjectForm)
    {
        $ds = $this
            ->select(
                "{$this->table}.order_id",
                "c.contract_id",
                "c.contract_code",
                "c.effective_date",
                "c.expired_date",
                "{$this->table}.amount_paid as amount"
            )
            ->join("orders as o", "o.order_id", "=", "{$this->table}.order_id")
            ->join("contract_map_order as cm", "cm.order_code", "=", "o.order_code")
            ->join("contracts as c", "c.contract_code", "=", "cm.contract_code")
            ->join("staffs as s", "s.staff_id", "=", "c.performer_by")
            ->join("contract_partner as p", "p.contract_id", "=", "c.contract_id")
            ->join("contract_category_status as st", "st.status_code", "=", "c.status_code")

            ->whereDate("{$this->table}.created_at", $date)
            ->where("{$this->table}.is_deleted", self::NOT_DELETED)
            ->where("c.is_deleted", self::NOT_DELETED)
            ->where("st.default_system", self::PROCESSING)
            ->whereIn("o.process_status", [self::PAY_SUCCESS, self::PAY_HALF])
            ->groupBy("c.contract_code");

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

        //Lấy giá trị theo hình thức hợp đồng
        if ($contractForm != null && $contractForm != 'all') {
            $ds->whereIn("c.contract_form", $contractForm);
        }

        //Lấy giá trị theo hình thức đối tác
        if ($partnerObjectForm != null && $partnerObjectForm != 'all') {
            $ds->where("p.partner_object_form", $partnerObjectForm);
        }

        return $ds->get();
    }

    /**
     * Lấy phiếu thu cho hợp đồng gia hạn
     *
     * @param $date
     * @param $scope
     * @param $scopeObjectId
     * @param $idCategory
     * @param $contractForm
     * @param $partnerObjectForm
     * @return mixed
     */
    public function getReceiptContractAnnexByDate($date, $scope, $scopeObjectId, $idCategory, $contractForm, $partnerObjectForm)
    {
        $ds = $this
            ->select(
                "{$this->table}.order_id",
                "c.contract_id",
                "c.contract_code",
                "a.effective_date",
                "c.expired_date",
                "{$this->table}.amount_paid as amount"
            )
            ->join("orders as o", "o.order_id", "=", "{$this->table}.order_id")
            ->join("contract_map_order as cm", "cm.order_code", "=", "o.order_code")
            ->join("contracts as c", "c.contract_code", "=", "cm.contract_code")
            ->join("contract_annex as a", "a.contract_id", "=", "c.contract_id")
            ->join("staffs as s", "s.staff_id", "=", "c.performer_by")
            ->join("contract_partner as p", "p.contract_id", "=", "c.contract_id")
            ->join("contract_category_status as st", "st.status_code", "=", "c.status_code")
            ->whereDate("{$this->table}.created_at", $date)
            ->where("{$this->table}.is_deleted", self::NOT_DELETED)
            ->where("c.is_deleted", self::NOT_DELETED)
            ->where("st.default_system", self::PROCESSING)
            ->whereIn("o.process_status", [self::PAY_SUCCESS, self::PAY_HALF])
            ->where("a.is_active", self::IS_ACTIVE)
            ->where("a.is_deleted", self::NOT_DELETED)
            ->where("a.adjustment_type", self::RENEW_CONTRACT)
            ->groupBy("c.contract_code");

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

        //Lấy giá trị theo hình thức hợp đồng
        if ($contractForm != null && $contractForm != 'all') {
            $ds->whereIn("c.contract_form", $contractForm);
        }

        //Lấy giá trị theo hình thức đối tác
        if ($partnerObjectForm != null && $partnerObjectForm != 'all') {
            $ds->where("p.partner_object_form", $partnerObjectForm);
        }

        return $ds->get();
    }
}
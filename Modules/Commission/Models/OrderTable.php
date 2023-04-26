<?php

namespace Modules\Commission\Models;

use Illuminate\Database\Eloquent\Model;

class OrderTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = "orders";
    protected $primaryKey = "order_id";

    const NOT_DELETED = 0;
    const PAY_SUCCESS = "paysuccess";
    const PAY_HALF = "pay-half";
    const PROCESSING = "processing";
    const RENEW_CONTRACT = "renew_contract";
    const IS_ACTIVE = 1;

    /**
     * Lấy đơn hàng trong ngày để tính hoa hồng
     *
     * @param $date
     * @param $scope
     * @param $scopeObjectId
     * @param $objectType
     * @param $objectCategoryId
     * @param $arrObjectId
     * @return mixed
     */
    public function getOrderInDate($date, $scope, $scopeObjectId, $objectType, $objectCategoryId, $arrObjectId)
    {
        $ds = $this
            ->select(
                "{$this->table}.order_id",
                "{$this->table}.order_code",
                "{$this->table}.process_status",
                "{$this->table}.total",
                "{$this->table}.discount",
                "{$this->table}.amount",
                "{$this->table}.tranport_charge",
                "pc.category_name as product_category_name",
                "sv_cate.name as service_category_name",
                "svc_group.name as service_card_category_name"
            )
            ->join("staffs as s", "s.staff_id", "=", "{$this->table}.created_by")
            ->join("order_details as d", "d.order_id", "=", "{$this->table}.order_id")
            ->leftJoin("product_childs as child", "child.product_child_id", "=", "d.object_id")
            ->leftJoin("products as p", "p.product_id", "=", "child.product_id")
            ->leftJoin("product_categories as pc", "pc.product_category_id", "=", "p.product_category_id")
            ->leftJoin("services as sv", "sv.service_id", "=", "d.object_id")
            ->leftJoin("service_categories as sv_cate", "sv_cate.service_category_id", "=", "sv.service_category_id")
            ->leftJoin("service_cards as svc", "svc.service_card_id", "=", "d.object_id")
            ->leftJoin("service_card_groups as svc_group", "svc_group.service_card_group_id", "=", "svc.service_card_group_id")
            ->whereIn("{$this->table}.process_status", [self::PAY_SUCCESS, self::PAY_HALF])
            ->whereDate("{$this->table}.created_at", $date)
            ->groupBy("{$this->table}.order_id");

        //Lấy theo giá trị của (cá nhân/ nhóm)
        switch ($scope) {
            case 'personal':
                $ds->where("{$this->table}.created_by", $scopeObjectId);
                break;
            case 'group';
                $ds->where("s.team_id", $scopeObjectId);
                break;
        }

        //Áp dụng cho loại hàng hoá
        if ($objectType != null && $objectType != 'all') {
            $ds->where("d.object_type", $objectType);
        }

        //Áp dụng cho nhóm hàng hoá
//        if ($objectType != null && $objectType != 'all' && $objectCategoryId != null && $objectCategoryId != 0) {
//            switch ($objectType) {
//                case 'product':
//                    $ds->where("pc.product_category_id", $objectCategoryId);
//                    break;
//                case 'service':
//                    $ds->where("sv_cate.service_category_id", $objectCategoryId);
//                    break;
//                case 'service_card':
//                    $ds->where("svc_group.service_card_group_id", $objectCategoryId);
//                    break;
//            }
//        }

        //Áp dụng đơn hàng có hàng hoá
        if (count($arrObjectId) > 0) {
            $ds->whereIn("d.object_id", $arrObjectId);
        }

        return $ds->get();
    }

    /**
     * Lấy đơn hàng đã hoàn thành (1 hay nhiều lần lấy ngày thanh toán cuối cùng)
     *
     * @param $date
     * @param $scope
     * @param $scopeObjectId
     * @param $objectType
     * @param $arrObjectId
     * @return mixed
     */
    public function getOrderSuccessByDate($date, $scope, $scopeObjectId, $objectType, $arrObjectId)
    {
        $ds = $this
            ->select(
                "{$this->table}.order_id",
                "{$this->table}.order_code",
                "{$this->table}.process_status",
                "{$this->table}.total",
                "{$this->table}.discount",
                "{$this->table}.amount",
                "{$this->table}.tranport_charge",
                "pc.category_name as product_category_name",
                "sv_cate.name as service_category_name",
                "svc_group.name as service_card_category_name"
            )
            ->join("order_details as d", "d.order_id", "=", "{$this->table}.order_id")
            ->join("receipts as r", "r.order_id", "=", "{$this->table}.order_id")
            ->join("staffs as s", "s.staff_id", "=", "{$this->table}.created_by")
            ->leftJoin("product_childs as child", "child.product_child_id", "=", "d.object_id")
            ->leftJoin("products as p", "p.product_id", "=", "child.product_id")
            ->leftJoin("product_categories as pc", "pc.product_category_id", "=", "p.product_category_id")
            ->leftJoin("services as sv", "sv.service_id", "=", "d.object_id")
            ->leftJoin("service_categories as sv_cate", "sv_cate.service_category_id", "=", "sv.service_category_id")
            ->leftJoin("service_cards as svc", "svc.service_card_id", "=", "d.object_id")
            ->leftJoin("service_card_groups as svc_group", "svc_group.service_card_group_id", "=", "svc.service_card_group_id")
            ->whereIn("{$this->table}.process_status", [self::PAY_SUCCESS])
            ->whereDate("r.created_at", $date)
            ->groupBy("{$this->table}.order_id")
            ->orderBy("r.receipt_id", "desc");

        //Lấy theo giá trị của (cá nhân/ nhóm)
        switch ($scope) {
            case 'personal':
                $ds->where("{$this->table}.created_by", $scopeObjectId);
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
     * Lấy doanh thu thu hợp đồng (mới, tái kí)
     *
     * @param $date
     * @param $scope
     * @param $scopeObjectId
     * @param $idCategory
     * @param $contractForm
     * @param $partnerObjectForm
     * @return mixed
     */
    public function getOrderContractSuccessByDate($date, $scope, $scopeObjectId, $idCategory, $contractForm, $partnerObjectForm)
    {
        $ds = $this
            ->select(
                "{$this->table}.order_id",
                "c.contract_id",
                "c.contract_code",
                "c.effective_date",
                "c.expired_date",
                "{$this->table}.amount"
            )
            ->join("receipts as r", "r.order_id", "=", "{$this->table}.order_id")
            ->join("contract_map_order as cm", "cm.order_code", "=", "{$this->table}.order_code")
            ->join("contracts as c", "c.contract_code", "=", "cm.contract_code")
            ->join("staffs as s", "s.staff_id", "=", "c.performer_by")
            ->join("contract_partner as p", "p.contract_id", "=", "c.contract_id")
            ->join("contract_category_status as st", "st.status_code", "=", "c.status_code")

            ->whereDate("r.created_at", $date)
            ->whereIn("{$this->table}.process_status", [self::PAY_SUCCESS])
            ->where("{$this->table}.is_deleted", self::NOT_DELETED)
            ->where("c.is_deleted", self::NOT_DELETED)
            ->where("st.default_system", self::PROCESSING)
            ->groupBy("c.contract_code")
            ->orderBy("r.receipt_id", "desc");

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
     * Lấy doanh thu thu hợp đồng gian hạn
     *
     * @param $date
     * @param $scope
     * @param $scopeObjectId
     * @param $idCategory
     * @param $contractForm
     * @param $partnerObjectForm
     * @return mixed
     */
    public function getOrderContractAnnexSuccessByDate($date, $scope, $scopeObjectId, $idCategory, $contractForm, $partnerObjectForm)
    {
        $ds = $this
            ->select(
                "{$this->table}.order_id",
                "c.contract_id",
                "c.contract_code",
                "c.effective_date",
                "c.expired_date",
                "{$this->table}.amount"
            )
            ->join("receipts as r", "r.order_id", "=", "{$this->table}.order_id")
            ->join("contract_map_order as cm", "cm.order_code", "=", "{$this->table}.order_code")
            ->join("contracts as c", "c.contract_code", "=", "cm.contract_code")
            ->join("contract_annex as a", "a.contract_id", "=", "c.contract_id")
            ->join("staffs as s", "s.staff_id", "=", "c.performer_by")
            ->join("contract_partner as p", "p.contract_id", "=", "c.contract_id")
            ->join("contract_category_status as st", "st.status_code", "=", "c.status_code")

            ->whereDate("r.created_at", $date)
            ->whereIn("{$this->table}.process_status", [self::PAY_SUCCESS])
            ->where("{$this->table}.is_deleted", self::NOT_DELETED)
            ->where("c.is_deleted", self::NOT_DELETED)
            ->where("st.default_system", self::PROCESSING)
            ->where("a.is_active", self::IS_ACTIVE)
            ->where("a.is_deleted", self::NOT_DELETED)
            ->where("a.adjustment_type", self::RENEW_CONTRACT)
            ->groupBy("c.contract_code")
            ->orderBy("r.receipt_id", "desc");

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
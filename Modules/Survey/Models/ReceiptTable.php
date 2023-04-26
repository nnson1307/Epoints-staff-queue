<?php

/**
 * Created by PhpStorm.
 * User: Mr Son
 * Date: 12/5/2018
 * Time: 2:37 PM
 */

namespace Modules\Survey\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use MyCore\Models\Traits\ListTableTrait;

class ReceiptTable extends Model
{
    use ListTableTrait;
    protected $connection = BRAND_CONNECTION;
    protected $table = "receipts";
    protected $primaryKey = "receipt_id";
    protected $fillable = [
        "receipt_id",
        "receipt_code",
        "customer_id",
        "staff_id",
        "order_id",
        "total_money",
        "voucher_code",
        "status",
        "discount",
        "custom_discount",
        "is_discount",
        "amount",
        "amount_paid",
        "amount_return",
        "note",
        "created_by",
        "updated_by",
        "created_at",
        "updated_at",
        "object_id",
        "object_type",
        "receipt_source",
        "receipt_type_code",
        "object_accounting_type_code",
        "object_accounting_id",
        "object_accounting_name",
        "type_insert",
        "document_code"
    ];


    const IS_DELETE = 0;
    const IS_ACTIVE = 1;
    const IS_VANGLAI = 1;
    const ARR_PAID_RECEIPT = ['part-paid', 'paid'];
    const NOT_DELETE = 0;

    public function add(array $data)
    {
        $add = $this->create($data);
        return $add->receipt_id;
    }

    public function getItem($id)
    {
        $ds = $this->leftJoin("staffs", "staffs.staff_id", "=", "receipts.created_by")
            ->select(
                "receipts.receipt_id",
                "receipts.receipt_code",
                "receipts.customer_id",
                "receipts.order_id",
                "receipts.amount",
                //                "receipts.amount_paid",
                DB::raw("SUM(amount_paid) as amount_paid"),
                "receipts.created_at",
                "receipts.created_by",
                "receipts.amount_return",
                "staffs.full_name"
            )->where("receipts.order_id", $id)->first();
        return $ds;
    }

    public function edit(array $data, $id)
    {
        return $this->where("receipt_id", $id)->update($data);
    }

    public function _getList(&$filters = [])
    {
        $select = $this
            ->select(
                "orders.order_code as order_code",
                "customers.full_name as customer_name",
                "staffs.full_name as staff_name",
                "receipts.created_at as created_at",
                "receipts.receipt_id",
                "receipts.amount",
                "receipts.amount_paid",
                "receipts.note"
            )
            ->leftJoin("staffs", "staffs.staff_id", "=", "receipts.staff_id")
            ->leftJoin("orders", "orders.order_id", "=", "receipts.order_id")
            ->leftJoin("customers", "customers.customer_id", "=", "receipts.customer_id")
            ->whereIn("receipts.status", ["part-paid", "unpaid"]);

        if (isset($filters["search_keyword"]) && $filters["search_keyword"] != "") {
            $keyword = $filters["search_keyword"];
            $select->where(function ($query) use ($keyword) {
                $query->where("customers.full_name", "like", "%" . $keyword . "%")
                    ->orWhere("orders.order_code", "like", "%" . $keyword . "%");
            });
            unset($filters["search_keyword"]);
        }
        return $select;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getReceipt($id)
    {
        $ds = $this
            ->leftJoin("staffs", "staffs.staff_id", "=", "receipts.staff_id")
            ->select(
                "receipts.receipt_id",
                "receipts.receipt_code",
                "receipts.customer_id",
                "receipts.object_id",
                "receipts.amount",
                "receipts.amount_paid",
                "receipts.amount_return",
                "staffs.full_name",
                "receipts.total_money",
                "receipts.custom_discount as discount",
                "receipts.created_at",
                "staffs.full_name"
            )
            ->where(function ($query) use ($id) {
                $query->where("object_type", "debt")
                    ->where("object_id", $id);
            })
            ->orWhere(function ($query) use ($id) {
                $query->where("receipt_type_code", "RTC_DEBT")
                    ->where("object_accounting_id", $id);
            })
            ->get();
        return $ds;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getAmountDebt($id)
    {
        $select = $this
            ->leftJoin("orders", "orders.order_id", "=", "receipts.order_id")
            ->leftJoin("staffs", "staffs.staff_id", "=", "receipts.created_by")
            ->select(
                "receipts.total_money",
                "receipts.custom_discount as discount",
                "receipts.amount",
                "receipts.amount_paid",
                "receipts.amount_return",
                "orders.order_code",
                "receipts.receipt_id",
                "staffs.full_name",
                "receipts.created_at"
            )
            ->where("receipts.customer_id", $id)
            ->whereIn("receipts.status", ["unpaid", "part-paid"])
            ->get();
        return $select;
    }

    public function getReceiptById($id)
    {
        $ds = $this->leftJoin("staffs", "staffs.staff_id", "=", "receipts.staff_id")
            ->select(
                "receipts.receipt_id",
                "receipts.receipt_code",
                "receipts.customer_id",
                "receipts.object_id",
                "receipts.amount",
                "receipts.amount_paid",
                "receipts.amount_return",
                "staffs.full_name",
                "receipts.total_money",
                "receipts.custom_discount as discount",
                "receipts.created_at",
                "staffs.full_name",
                "receipts.note"
            )
            ->where("receipts.receipt_id", $id)
            ->where("receipts.object_type", "debt")
            ->first();
        return $ds;
    }

    /**
     * Lấy tất cả tiền đã thanh toán của đơn hàng
     *
     * @return mixed
     */
    public function getAllReceipt()
    {
        return $this
            ->select(
                "order_id",
                DB::raw("SUM(amount_paid) as amount_paid"),
                "note"
            )
            ->whereNotIn("status", ["cancel", "fail"])
            ->groupBy("order_id")
            ->get();
    }

    public function getAmountPaidByOrderId($orderId)
    {
        return $this
            ->select(
                "order_id",
                DB::raw("SUM(amount_paid) as amount_paid"),
                "note"
            )
            ->whereNotIn("status", ["cancel", "fail"])
            ->where("order_id", $orderId)
            ->groupBy("order_id")
            ->first();
    }

    /**
     * Lấy thông tin thanh toán của đơn hàng
     *
     * @param $orderId
     * @return mixed
     */
    public function getReceiptOrder($orderId)
    {
        return $this
            ->select(
                "order_id",
                DB::raw("SUM(amount_paid) as amount_paid"),
                "note"
            )
            ->whereNotIn("status", ["cancel", "fail"])
            ->where("order_id", $orderId)
            ->groupBy("order_id")
            ->first();
    }

    /**
     * Lấy tất cả thanh toán của đơn hàng
     *
     * @param $orderId
     * @return mixed
     */
    public function getReceiptByOrder($orderId)
    {
        $lang = Config::get('app.locale');

        return $this
            ->select(
                "{$this->table}.receipt_id",
                "{$this->table}.receipt_code",
                "{$this->table}.customer_id",
                "{$this->table}.staff_id",
                "{$this->table}.order_id",
                "{$this->table}.total_money",
                "{$this->table}.voucher_code",
                "{$this->table}.status",
                "{$this->table}.discount",
                "{$this->table}.custom_discount",
                "{$this->table}.is_discount",
                "{$this->table}.amount",
                "{$this->table}.amount_paid",
                "{$this->table}.amount_return",
                "{$this->table}.note",
                "{$this->table}.object_id",
                "{$this->table}.object_type",
                "{$this->table}.receipt_type_code",
                "{$this->table}.object_accounting_type_code",
                "{$this->table}.object_accounting_id",
                "{$this->table}.object_accounting_name",
                "{$this->table}.created_by",
                "staffs.full_name",
                "receipt_type.receipt_type_name_$lang as receipt_type_name",
                "{$this->table}.created_at",
                "{$this->table}.updated_at"
            )
            ->leftJoin("staffs", "staffs.staff_id", "=", "receipts.created_by")
            ->leftJoin("receipt_type", "receipt_type.receipt_type_code", "=", "{$this->table}.receipt_type_code")
            ->where("receipts.order_id", $orderId)
            ->get();
    }

    //    public function getListReceiptByOrder($arrOrderId){
    //        $oSelect = $this->whereIn('order_id',$arrOrderId)->get();
    //        return $oSelect;
    //    }
    public function getListReceiptByOrder($startTime, $endTime, $filer, $valueFilter, $customerGroup)
    {
        $oSelect = $this
            ->join('orders', 'orders.order_id', 'receipts.order_id')
            ->join('customers', 'customers.customer_id', 'receipts.customer_id');
        if ($filer == null && $valueFilter == null) {
            $oSelect->whereBetween(
                'receipts.created_at',
                [$startTime . " 00:00:00", $endTime . " 23:59:59"]
            );
        } else {
            $oSelect->whereBetween(
                'receipts.created_at',
                [$startTime . " 00:00:00", $endTime . " 23:59:59"]
            )
                ->where($filer, $valueFilter)->where('orders.is_deleted', 0);
        }

        if ($customerGroup != null) {
            $oSelect->where('customers.customer_group_id', $customerGroup);
        }

        return $oSelect->select(
            //            'receipts.*',
            "receipts.receipt_id",
            "receipts.customer_id",
            "receipts.order_id",
            "receipts.total_money",
            "receipts.status",
            "receipts.amount",
            "receipts.amount_paid",
            "receipts.created_by",
            //            "receipts.created_at",
            DB::raw("(DATE_FORMAT(receipts.created_at,'%Y-%m-%d') ) as created_at"),
            "orders.branch_id"
        )->get();
    }

    public function getReceiptOrderId($orderId)
    {
        $oSelect = $this->where('order_id', $orderId)->first();
        return $oSelect;
    }

    /**
     * top .... KH có doanh thu cao nhất
     *
     * @param null $limit
     * @return mixed
     */
    public function getTopHighRevenueOfCustomer($limit = null)
    {
        $data = $this->select(
            "{$this->table}.customer_id",
            DB::raw("SUM({$this->table}.total_money) as total_money")
        )
            ->leftJoin("customers", "customers.customer_id", "{$this->table}.customer_id")
            ->where('customers.customer_id', '!=', self::IS_VANGLAI)
            ->where('customers.is_deleted', self::IS_DELETE)
            ->where('customers.is_actived', self::IS_ACTIVE)
            ->whereIn("{$this->table}.status", self::ARR_PAID_RECEIPT)
            ->groupBy("{$this->table}.customer_id")
            ->orderBy("total_money", "DESC");
        if ($limit != null) {
            $data->limit($limit);
        }
        return $data->get();
    }

    /**
     * top ... KH có doanh thu thấp nhất
     *
     * @param null $limit
     * @return mixed
     */
    public function getTopLowRevenueOfCustomer($limit = null)
    {
        $data = $this->select(
            "{$this->table}.customer_id",
            DB::raw("SUM({$this->table}.total_money) as total_money")
        )
            ->leftJoin("customers", "customers.customer_id", "{$this->table}.customer_id")
            ->where('customers.customer_id', '!=', self::IS_VANGLAI)
            ->where('customers.is_deleted', self::IS_DELETE)
            ->where('customers.is_actived', self::IS_ACTIVE)
            ->whereIn("{$this->table}.status", self::ARR_PAID_RECEIPT)
            ->groupBy("{$this->table}.customer_id")
            ->orderBy("total_money", "ASC");
        if ($limit != null) {
            $data->limit($limit);
        }
        return $data->get();
    }

    public function removeReceipt($orderId)
    {
        return $this->where("order_id", $orderId)->delete();
    }

    /**
     * Lấy data phiếu thu export excel
     *
     * @param $beforeDate
     * @return mixed
     */
    public function getReceiptExportSie($beforeDate)
    {
        $lang = Config::get('app.locale');

        return $this
            ->select(
                "{$this->table}.receipt_id",
                "{$this->table}.receipt_code",
                "{$this->table}.customer_id",
                "{$this->table}.staff_id",
                "{$this->table}.order_id",
                "{$this->table}.total_money",
                "{$this->table}.voucher_code",
                "{$this->table}.status",
                "{$this->table}.discount",
                "{$this->table}.custom_discount",
                "{$this->table}.is_discount",
                "{$this->table}.amount",
                "{$this->table}.amount_paid",
                "{$this->table}.amount_return",
                "{$this->table}.note",
                "{$this->table}.object_id",
                "{$this->table}.object_type",
                "{$this->table}.receipt_type_code",
                "{$this->table}.object_accounting_type_code",
                "{$this->table}.object_accounting_id",
                "{$this->table}.object_accounting_name",
                "{$this->table}.type_insert",
                "{$this->table}.created_at",
                "{$this->table}.updated_at",
                "receipt_type.receipt_type_name_$lang as receipt_type_name",
                "oat.object_accounting_type_name_$lang as object_accounting_type_name",
                "staffs.full_name as staff_name",
                "cs.full_name as customer_name",
                "cs1.full_name as customer_name_debt"
            )
            ->leftJoin("receipt_type", "receipt_type.receipt_type_code", "=", "{$this->table}.receipt_type_code")
            ->leftJoin("object_accounting_type as oat", "oat.object_accounting_type_code", "=", "{$this->table}.object_accounting_type_code")
            ->leftJoin("staffs", "staffs.staff_id", "=", "{$this->table}.created_by")
            ->leftJoin("orders as or", "or.order_id", "=", "{$this->table}.order_id")
            ->leftJoin("customers as cs", "cs.customer_id", "=", "or.customer_id")
            ->leftJoin("customers as cs1", "cs1.customer_id", "=", "{$this->table}.customer_id")
            ->where("{$this->table}.is_deleted", self::NOT_DELETE)
            ->whereDate("{$this->table}.created_at", "<", $beforeDate)
            ->get();
    }

    /**
     * Lấy lần thanh toán gần nhất của đơn hàng
     *
     * @param $orderId
     * @return mixed
     */
    public function getReceiptOrderLast($orderId)
    {
        return $this
            ->select(
                "{$this->table}.receipt_id",
                "{$this->table}.receipt_code",
                "{$this->table}.customer_id",
                "{$this->table}.staff_id",
                "{$this->table}.order_id",
                "{$this->table}.total_money",
                "{$this->table}.voucher_code",
                "{$this->table}.status",
                "{$this->table}.discount",
                "{$this->table}.custom_discount",
                "{$this->table}.is_discount",
                "{$this->table}.amount",
                "{$this->table}.amount_paid",
                "{$this->table}.amount_return",
                "{$this->table}.note",
                "{$this->table}.object_id",
                "{$this->table}.object_type",
                "{$this->table}.receipt_type_code",
                "{$this->table}.object_accounting_type_code",
                "{$this->table}.object_accounting_id",
                "{$this->table}.object_accounting_name",
                "{$this->table}.created_at"
            )
            ->where("receipts.order_id", $orderId)
            ->orderBy("{$this->table}.receipt_id", "desc")
            ->first();
    }
}

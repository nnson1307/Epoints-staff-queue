<?php
/**
 * Created by PhpStorm.
 * User: LE DANG SINH
 * Date: 10/5/2018
 * Time: 11:24 AM
 */

namespace Modules\Survey\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use MyCore\Models\Traits\ListTableTrait;

class ProductChildTable extends Model
{
    use ListTableTrait;
    protected $connection = BRAND_CONNECTION;
    protected $table = 'product_childs';
    protected $primaryKey = 'product_child_id';
    protected $fillable
        = [
            'product_child_id', 'product_id',
            'product_code', 'product_child_name',
            'unit_id', 'cost', 'price', 'created_at', 'updated_at',
            'created_by', 'updated_by', 'is_deleted', 'is_actived', 'slug',
            'is_sales', 'type_app', 'percent_sale','inventory_management',
            'is_display'
        ];

    const IS_NOT_DELETED = 0;
    const IS_ACTIVE = 1;
    const IS_SALE = 1;

    protected function _getList()
    {
        return $this
            ->select('product_child_id', 'product_id', 'product_code', 'product_child_name',
                'unit_id', 'cost', 'price', 'created_at', 'updated_at', 'created_by', 'updated_by', 'is_deleted', 'is_actived')
            ->where('is_deleted', self::IS_NOT_DELETED)->orderBy($this->primaryKey, 'desc');
    }

    //Add product child
    public function add(array $data)
    {
        $productChild = $this->create($data);
        return $productChild->product_child_id;
    }

    /*
     * Delete product child
     */
    public function remove($id)
    {
        return $this->where($this->primaryKey, $id)->update(['is_deleted' => 1]);
    }

    /*
    * Edit product child
    */
    public function edit(array $data, $id)
    {
        return $this->where($this->primaryKey, $id)->update($data);
    }

    /*
     * get item
     */
    public function getItem($id)
    {
        return $this
            ->leftJoin('products', 'products.product_id', '=', 'product_childs.product_id')
            ->leftJoin('units', 'units.unit_id', '=', 'product_childs.unit_id')
            ->select(
                'product_childs.product_child_id',
                'product_childs.product_id',
                'product_childs.product_code',
                'product_childs.product_child_name',
                'product_childs.unit_id',
                'units.name as unit_name',
                'product_childs.cost',
                'product_childs.price',
                'product_childs.created_at',
                'product_childs.updated_at',
                'product_childs.created_by',
                'product_childs.updated_by',
                'product_childs.is_deleted',
                'product_childs.is_actived',
                'product_childs.slug',
                'products.type_refer_commission',
                'products.refer_commission_value',
                'products.type_staff_commission',
                'products.staff_commission_value',
                'product_childs.is_sales',
                'product_childs.type_app',
                'product_childs.percent_sale',
                "{$this->table}.is_remind",
                "{$this->table}.remind_value"
            )
            ->where('product_childs.product_child_id', $id)
            ->first();
    }

    /*
     * Test product code
     */
    public function testProductCode($code)
    {
        return $this->where('product_code', $code)
            ->where('is_deleted', self::IS_NOT_DELETED)
            ->first();
    }

    /*
     * get product child by product id
     */
    public function getProductChildByProductId($id)
    {
        $select = $this->leftJoin('products', 'products.product_id', '=', 'product_childs.product_id')
            ->select(
                'product_childs.product_child_id as product_child_id',
                'product_childs.product_id as product_id',
                'product_childs.product_code as product_code',
                'product_childs.product_child_name as product_child_name',
                'product_childs.unit_id as unit_id',
                'product_childs.cost as cost',
                'product_childs.price as price',
                'product_childs.is_display',
                'product_childs.created_at as created_at',
                'product_childs.updated_at as updated_at',
                'product_childs.created_by as created_by',
                'product_childs.updated_by as updated_by',
                'product_childs.is_deleted as is_deleted',
                'product_childs.is_deleted as is_deleted',
                'product_childs.is_actived as is_actived',
                DB::raw("(SELECT SUM(product_inventorys.quantity) FROM product_inventorys where product_inventorys.product_code = product_childs.product_code) as total_warehouse")
                )
            ->where('product_childs.product_id', $id)
            ->where('products.is_deleted', self::IS_NOT_DELETED)
            ->where('product_childs.is_deleted', self::IS_NOT_DELETED)
            ->get();
        return $select;
    }

    /*
     *search product child
     */
    public function searchProductChild($name)
    {
        $select = $this->leftJoin('products', 'products.product_id', '=', 'product_childs.product_id')
            ->select('product_childs.product_child_id',
                'product_childs.product_child_name',
                'product_childs.price',
                'products.avatar')
            ->where([
                ['product_childs.product_child_name', 'like', '%' . $name . '%'],
                ['product_childs.is_deleted', self::IS_NOT_DELETED],
                ['products.is_deleted', self::IS_NOT_DELETED]
            ])
            ->orWhere([
                ['product_childs.product_code', 'like', '%' . $name . '%'],
                ['product_childs.is_deleted', self::IS_NOT_DELETED],
                ['products.is_deleted', self::IS_NOT_DELETED]
            ])
            ->get();
        return $select;

    }

    /*
     * get product child by id
     */
    public function getProductChildById($id)
    {
        $select = $this->leftJoin('products', 'products.product_id', '=', 'product_childs.product_id')
            ->select(
                'product_childs.product_child_id as product_child_id',
                'product_childs.product_id as product_id',
                'product_childs.product_code as product_code',
                'product_childs.product_child_name as product_child_name',
                'product_childs.unit_id as unit_id',
                'product_childs.cost as cost',
                'product_childs.price as price',
                'product_childs.inventory_management as inventory_management',
                'product_childs.created_at as created_at',
                'product_childs.updated_at as updated_at',
                'product_childs.created_by as created_by',
                'product_childs.updated_by as updated_by',
                'product_childs.is_deleted as is_deleted',
                'product_childs.is_actived as is_actived')
            ->where('product_childs.product_child_id', $id)
            ->where('products.is_deleted', self::IS_NOT_DELETED)
            ->where('product_childs.is_deleted', self::IS_NOT_DELETED)
            ->first();
        return $select;
    }

    /*
     * get product child by code
     */
    public function getProductChildByCode($code)
    {
        $select = $this->leftJoin('products', 'products.product_id', '=', 'product_childs.product_id')
            ->select(
                'product_childs.product_child_id as product_child_id',
                'product_childs.product_id as product_id',
                'product_childs.product_code as product_code',
                'product_childs.product_child_name as product_child_name',
                'product_childs.unit_id as unit_id',
                'product_childs.cost as cost',
                'product_childs.price as price',
                'product_childs.inventory_management as inventory_management',
                'product_childs.created_at as created_at',
                'product_childs.updated_at as updated_at',
                'product_childs.created_by as created_by',
                'product_childs.updated_by as updated_by',
                'product_childs.is_deleted as is_deleted',
                'product_childs.is_actived as is_actived')
            ->where('product_childs.product_code', $code)
            ->where('products.is_deleted', self::IS_NOT_DELETED)
            ->where('product_childs.is_deleted', self::IS_NOT_DELETED)
            ->first();
        return $select;
    }

    public function searchProductChildInventoryOutput($warehouseId, $name)
    {
        $select = $this
            ->leftJoin('product_inventorys', 'product_inventorys.product_code', '=', 'product_childs.product_code')
            ->where([
                ['product_inventorys.warehouse_id', $warehouseId],
                ['product_childs.product_child_name', 'like', '%' . $name . '%'],
                ['product_childs.is_deleted', self::IS_NOT_DELETED],
                ['product_inventorys.quantity', '>', 0]
            ])
            ->orWhere([
                ['product_inventorys.warehouse_id', $warehouseId],
                ['product_childs.product_code', 'like', '%' . $name . '%'],
                ['product_childs.is_deleted', self::IS_NOT_DELETED],
                ['product_inventorys.quantity', '>', 0]
            ])
            ->get();
        return $select;
    }

    /*
     * get product child by warehouse and code.
     */
    public function getProductChildByWarehouseAndCode($warehouseId, $code)
    {
        $select = $this->leftJoin('product_inventorys', 'product_inventorys.product_code', '=', 'product_childs.product_code')
            ->select(
                'product_childs.product_child_name as name',
                'product_inventorys.product_code as code',
                'product_childs.unit_id as unit',
                'product_childs.cost as cost',
                'product_childs.price as price',
                'product_inventorys.quantity as quantity'
            )
            ->where('product_inventorys.warehouse_id', $warehouseId)
            ->where('product_inventorys.product_code', $code)
//            ->where('product_inventorys.quantity', '>', 0)
            ->where('product_childs.is_deleted', self::IS_NOT_DELETED)
            ->first();
        return $select;
    }

    /*
     * search product child by warehouse and code.
     */
    public function searchProductChildByWarehouseAndCode($warehouseId, $code)
    {

        $select = $this
            ->leftJoin('products', 'products.product_id', '=', 'product_childs.product_id')
            ->leftJoin('product_branch_prices', 'product_branch_prices.product_id', '=', 'products.product_id')
            ->leftJoin('branches', 'branches.branch_id', '=', 'product_branch_prices.branch_id')
            ->leftJoin('warehouses', 'warehouses.branch_id', '=', 'branches.branch_id')
            ->where(function ($query) use ($warehouseId, $code) {
                $query->where(function ($query) use ($code) {
                    $query->where('product_childs.product_code', 'like', '%' . $code . '%')
                        ->orWhere('product_childs.product_child_name', 'like', '%' . $code . '%');
                });
            })
            ->where(function ($query) use ($warehouseId) {
                $query->where('warehouses.warehouse_id', $warehouseId);
            })->where('product_childs.is_deleted', self::IS_NOT_DELETED)
            ->get();
        return $select;
    }

    public function getProductChildByWarehouseAndProductCode($warehouseId, $code)
    {
        $select = $this
            ->leftJoin('products', 'products.product_id', '=', 'product_childs.product_id')
            ->leftJoin('product_branch_prices', 'product_branch_prices.product_id', '=', 'products.product_id')
            ->leftJoin('branches', 'branches.branch_id', '=', 'product_branch_prices.branch_id')
            ->leftJoin('warehouses', 'warehouses.branch_id', '=', 'branches.branch_id')
            ->select(
                'product_childs.unit_id as product_childs',
                'product_childs.product_code as product_code',
                'product_childs.product_code as product_code',
                'product_childs.cost as cost',
                'product_childs.product_child_name as product_child_name'
            )
            ->where([
                ['warehouses.warehouse_id', $warehouseId],
                ['product_childs.product_code', $code]
            ])->first();
        return $select;
    }

    public function getProductChildOption()
    {
        $select = $this->join('products', 'products.product_id', '=', 'product_childs.product_id')
            ->select(
                'product_childs.product_child_id as product_child_id',
                'product_childs.product_id as product_id',
                'product_childs.product_code as product_code',
                'product_childs.product_child_name as product_child_name',
                'product_childs.unit_id as unit_id',
                'product_childs.cost as cost',
                'product_childs.price as price',
                'product_childs.created_at as created_at',
                'product_childs.updated_at as updated_at',
                'product_childs.created_by as created_by',
                'product_childs.updated_by as updated_by',
                'product_childs.is_deleted as is_deleted',
                'product_childs.is_actived as is_actived')
            ->where('products.is_deleted', self::IS_NOT_DELETED)
            ->where('product_childs.is_deleted', self::IS_NOT_DELETED)
            ->get();
        return $select;
    }

    public function getOptionChildSonService()
    {
        $select = $this->leftJoin('products', 'products.product_id', '=', 'product_childs.product_id')
            ->select(
                'product_childs.product_child_id as product_child_id',
                'product_childs.product_id as product_id',
                'product_childs.product_code as product_code',
                'product_childs.product_child_name as product_child_name',
                'product_childs.unit_id as unit_id',
                'product_childs.cost as cost',
                'product_childs.price as price',
                'product_childs.created_at as created_at',
                'product_childs.updated_at as updated_at',
                'product_childs.created_by as created_by',
                'product_childs.updated_by as updated_by',
                'product_childs.is_deleted as is_deleted',
                'product_childs.is_actived as is_actived')
            ->where('products.is_deleted', self::IS_NOT_DELETED)
            ->where('product_childs.is_deleted', self::IS_NOT_DELETED)
            ->get();
        return $select;
    }

    //search product by keyword
    public function searchProduct($keyword)
    {
        $select = $this->where('is_deleted', self::IS_NOT_DELETED)
            ->where('product_code', 'like', '%' . $keyword . '%')
            ->orWhere('product_child_name', 'like', '%' . $keyword . '%')
            ->get();
        return $select;
    }

    public function getListChildOrder($productName, $productCategory)
    {
        $ds = $this->leftJoin('products', 'products.product_id', '=', 'product_childs.product_id')
            ->leftJoin('product_categories', 'product_categories.product_category_id', '=', 'products.product_category_id')
            ->leftJoin('units', 'units.unit_id', '=', 'product_childs.unit_id')
            ->select('product_childs.product_child_id', 'product_childs.product_code',
                'product_childs.product_child_name',
                'product_childs.price',
                'units.name',
                'product_childs.product_child_id as product_id', 'products.avatar as avatar',
                'product_childs.product_code as product_code',
                'product_categories.category_name as category_name')
            ->where('product_childs.is_deleted', self::IS_NOT_DELETED)
            ->where('products.is_deleted', self::IS_NOT_DELETED)
            ->where('products.is_actived', self::IS_ACTIVE);
        if ($productName != null) {
            $ds->where('product_childs.product_child_name', 'LIKE', '%' . $productName . '%');
        }
        if ($productCategory != null) {
            $ds->where('products.product_category_id', $productCategory);
        }
        $ds->where('product_childs.is_deleted', self::IS_NOT_DELETED)
            ->where('product_categories.is_deleted', self::IS_NOT_DELETED);
        return $ds->get();
    }

    public function getListChildOrderPaginate($param)
    {
        $page = (int)(isset($param['page']) ? $param['page'] : 1);
        $display = (int)(isset($param['perpage']) ? $param['perpage'] : FILTER_ITEM_PAGE);

        $ds = $this
            ->leftJoin('products', 'products.product_id', '=', 'product_childs.product_id')
            ->leftJoin('product_categories', 'product_categories.product_category_id', '=', 'products.product_category_id')
            ->leftJoin('units', 'units.unit_id', '=', 'product_childs.unit_id')
            ->select('product_childs.product_child_id', 'product_childs.product_code',
                'product_childs.product_child_name',
                'product_childs.price',
                'units.name',
                'product_childs.product_child_id as product_id', 'products.avatar as avatar',
                'product_childs.product_code as product_code',
                'product_categories.category_name as category_name')
            ->where('product_childs.is_deleted', self::IS_NOT_DELETED)
            ->where('products.is_deleted', self::IS_NOT_DELETED)
            ->where('products.is_actived', self::IS_ACTIVE);
        if (isset($param['search_keyword'])) {
            $ds->where('product_childs.product_child_name', 'LIKE', '%' . $param['search_keyword'] . '%');
        }
        if (isset($param['products$product_category_id'])) {
            $ds->where('products.product_category_id', $param['products$product_category_id']);
        }
        $ds->where('product_childs.is_deleted', self::IS_NOT_DELETED)
            ->where('product_categories.is_deleted', self::IS_NOT_DELETED);

        return $ds->paginate($display, $columns = ['*'], $pageName = 'page', $page);
    }

    public function getListChildOrderSearch($search)
    {
        $ds = $this->leftJoin('products', 'products.product_id', '=', 'product_childs.product_id')
            ->leftJoin('product_categories', 'product_categories.product_category_id', '=', 'products.product_category_id')
            ->leftJoin('units', 'units.unit_id', '=', 'product_childs.unit_id')
            ->select('product_childs.product_child_id',
                'product_childs.product_code',
                'product_childs.product_child_name',
                'product_childs.price',
                'units.name',
                'product_childs.product_id', 'products.avatar as avatar',
                'product_childs.product_code as product_code',
                'product_categories.category_name as category_name')
            ->where('product_childs.product_child_name', 'like', '%' . $search . '%')
            ->where('product_childs.is_deleted', self::IS_NOT_DELETED)
            ->where('products.is_deleted', self::IS_NOT_DELETED)
            ->where('products.is_actived', self::IS_ACTIVE);
        return $ds->get();
    }

    public function removeByCode($code)
    {
        return $this->where('product_child_name', $code)->update(['is_deleted' => 1]);
    }

    public function updateOrCreates(array $condition, array $data)
    {
        return $this->updateOrCreate($condition, $data);
    }

    public function getAllVoucher($filter = [])
    {
        $oSelect = $this->leftJoin('products', 'products.product_id', '=', 'product_childs.product_id')
            ->leftJoin('product_categories', 'product_categories.product_category_id', '=', 'products.product_category_id')
            ->select(
                'product_categories.product_category_id as proCategoryId',
                'product_categories.category_name as proCategoryName',
                'product_childs.product_child_name as proName',
                'product_childs.cost as proCost',
                'products.type as proType',
                'product_childs.product_child_id as proId'
            )
            ->where('product_childs.is_deleted', self::IS_NOT_DELETED)
            ->where("product_childs.is_actived", self::IS_ACTIVE)
            ->groupBy('product_childs.product_child_id');


        if (isset($filter["product_type"]) && $filter["product_type"] != "") {
            $oSelect->where("product_categories.product_category_id", $filter["product_type"]);
        }

        if (isset($filter["keyword"]) && $filter["keyword"] != "") {
            $oSelect->where("product_childs.product_child_name", "LIKE", "%" . $filter["keyword"] . "%");
        }


        return $oSelect->get();
    }

    public function updateByCode(array $data, $code)
    {
        return $this->where('product_code', $code)->update($data);
    }

    public function getProductChildOptionIdName()
    {
        return $this->leftJoin(
            'products',
            'products.product_id',
            'product_childs.product_id')
            ->select(
                'product_childs.product_child_id as product_child_id',
                'product_childs.product_child_name as product_child_name',
                'product_childs.product_code as product_code'
            )
            ->where('products.is_deleted', self::IS_NOT_DELETED)
            ->where('product_childs.is_deleted', self::IS_NOT_DELETED)
            ->get();
    }

    public function getProductChildInventoryOutput($warehouseId)
    {
        $select = $this->leftJoin('products', 'products.product_id', '=', 'product_childs.product_id')
            ->select(
                'product_childs.product_child_id as product_child_id',
                'product_childs.product_child_name as product_child_name',
                'product_childs.product_code as product_code'
            )
            ->leftJoin('product_inventorys', 'product_inventorys.product_code', '=', 'product_childs.product_code')
//            ->where('product_inventorys.warehouse_id', $warehouseId)
            ->where('products.is_deleted', self::IS_NOT_DELETED)
            ->where('product_childs.is_deleted', self::IS_NOT_DELETED)
            ->where("products.is_actived", self::IS_ACTIVE)
            ->where("product_childs.is_actived", self::IS_ACTIVE)
            ->get();
        return $select;
    }

    public function getProductChildByBranchesWarehouses($warehouseId)
    {
        $select = $this->select('product_child_id', 'product_child_name',$this->table.'.product_code')
            ->leftJoin('products', 'products.product_id', '=', 'product_childs.product_id')
            ->leftJoin('product_branch_prices', 'product_branch_prices.product_id', '=', 'products.product_id')
            ->leftJoin('branches', 'branches.branch_id', '=', 'product_branch_prices.branch_id')
            ->leftJoin('warehouses', 'warehouses.branch_id', '=', 'branches.branch_id')
            ->where(function ($query) use ($warehouseId) {
                $query->where('warehouses.warehouse_id', $warehouseId);
            })->where('product_childs.is_deleted', self::IS_NOT_DELETED)
            ->get();
        return $select;
    }

    public function checkProductChildName($name)
    {
        return $this->where('product_child_name', $name)
            ->where('is_deleted', self::IS_NOT_DELETED)
            ->first();
    }

    public function checkSlug($slug)
    {
        return $this->where('slug', $slug)->first();
    }

    public function getProductChildInId($arr_id)
    {
        $oSelect = $this->leftJoin('products', 'products.product_id', '=', 'product_childs.product_id')
            ->leftJoin('product_categories', 'product_categories.product_category_id', '=', 'products.product_category_id')
            ->select(
                'product_childs.product_child_id as productId',
                'product_childs.product_child_name as productName'
            )
            ->where('products.is_deleted', self::IS_NOT_DELETED)
            ->where("products.is_actived", self::IS_ACTIVE)
            ->where('product_childs.is_deleted', self::IS_NOT_DELETED)
            ->where("product_childs.is_actived", self::IS_ACTIVE)
            ->whereIn('product_childs.product_child_id', $arr_id)
            ->get();
        return $oSelect;
    }

    public function getProductChildInId2($arr_id)
    {
        $oSelect = $this->leftJoin('products', 'products.product_id', '=', 'product_childs.product_id')
            ->leftJoin('product_categories', 'product_categories.product_category_id', '=', 'products.product_category_id')
            ->select(
                'product_childs.product_child_id as productId',
                'product_childs.product_child_name as productName',
                'product_categories.category_name as categoryName',
                'product_childs.price as price'
            )
            ->where('products.is_deleted', self::IS_NOT_DELETED)
            ->where("products.is_actived", self::IS_ACTIVE)
            ->where('product_childs.is_deleted', self::IS_NOT_DELETED)
            ->where("product_childs.is_actived", self::IS_ACTIVE)
            ->whereIn('product_childs.product_child_id', $arr_id)
            ->get();
        return $oSelect;
    }

    public function removeByProductId($id)
    {
        return $this->where('product_id', $id)->update(['is_deleted' => self::IS_NOT_DELETED]);
    }

    /**
     * Danh sách product child.
     * @param array $filters
     *
     * @return mixed
     */
    public function getListCore(&$filters = [])
    {
        $select = $this->select(
            $this->table . '.*',
            'product_categories.category_name',
            'product_model.product_model_name'
        )
            ->join(
                'products',
                'products.product_id',
                $this->table . '.product_id')
            ->join('product_categories',
                'product_categories.product_category_id',
                'products.product_category_id')
            ->leftJoin('product_model',
                'product_model.product_model_id',
                'products.product_model_id')
            ->leftJoin('product_branch_prices',
                'product_branch_prices.product_id',
                'products.product_id')
            ->leftJoin('branches',
                'branches.branch_id',
                'product_branch_prices.branch_id')
            ->where($this->table . '.is_deleted', self::IS_NOT_DELETED)
            ->where('products.is_deleted', self::IS_NOT_DELETED)
            ->where('product_categories.is_deleted', self::IS_NOT_DELETED)
            ->groupBy($this->table . '.' . $this->primaryKey)
            ->orderBy($this->table . '.' . $this->primaryKey, 'desc');
        if (isset($filters['type_tab']) && $filters['type_tab'] === 'new') {
            $select->where($this->table . '.type_app',
                'like', '%' . $filters['type_tab'] . '%'
            );
        }
        if (isset($filters['type_tab']) && $filters['type_tab'] === 'best_seller') {
            $select->where($this->table . '.type_app',
                'like', '%' . $filters['type_tab'] . '%'
            );
        }
        if (isset($filters['type_tab']) && $filters['type_tab'] === 'sale') {
            $select->where($this->table . '.is_sales', self::IS_SALE);
        }
        if (isset($filters['created_at'])) {
            $select->whereBetween($this->table . '.created_at', $filters['created_at']);
            unset($filters['created_at']);
        }
        if (isset($filters['arrayNotIn'])) {
            $select->whereNotIn($this->table . '.' . $this->primaryKey, $filters['arrayNotIn']);
            unset($filters['arrayNotIn']);
        }
        unset($filters['type_tab']);
        return $select;
    }

    public function getWhereIn($arrayId)
    {
        $select = $this->select($this->table . '.*')
            ->whereIn($this->table . '.' . $this->primaryKey, $arrayId)
            ->get();
        return $select;
    }

    /**
     * Lấy thông tin product child
     *
     * @param $childCode
     * @return mixed
     */
    public function getChildByCode($childCode)
    {
        return $this
            ->select(
                "product_child_id",
                "product_id",
                "product_code",
                "product_child_name"
            )
            ->where("product_code", $childCode)
            ->where("is_actived", self::IS_ACTIVE)
            ->where("is_deleted", self::IS_NOT_DELETED)
            ->first();
    }

    /**
     * Danh sách sản phẩm
     * @return mixed
     */
    public function getList($filter = [])
    {
        $select = $this->select(
            'product_childs.product_child_id as product_child_id',
            'product_childs.product_id as product_id',
            'product_childs.product_code as product_code',
            'product_childs.product_child_name as product_child_name',
            'product_childs.unit_id as unit_id',
            'product_childs.cost as cost',
            'product_childs.price as price',
            'product_childs.created_at as created_at',
            'product_childs.updated_at as updated_at',
            'product_childs.created_by as created_by',
            'product_childs.updated_by as updated_by',
            'product_childs.is_deleted as is_deleted',
            'product_childs.is_actived as is_actived')
            ->join('products', 'products.product_id', '=', 'product_childs.product_id')
            ->where('products.is_deleted', self::IS_NOT_DELETED)
            ->where('product_childs.is_deleted', self::IS_NOT_DELETED);
        if (isset($filter['keyword']) && $filter['keyword'] != null) {
            $select->where(function ($query) use ($filter) {
                $query->where("product_childs.product_child_name",
                    "LIKE", "%" . $filter["keyword"] . "%")
                    ->orWhere("product_childs.product_code",
                        "LIKE", "%" . $filter["keyword"] . "%");
            });
        }
        return $select->paginate(
            $filter['perpage'],
            $columns = ['*'],
            $pageName = 'page',
            $filter['page']
        );
    }

    /**
     * Danh sách option của product child load more theo trang
     * @param array $filter
     *
     * @return mixed
     */
    public function getProductChildOptionPage($filter = [])
    {
        $select = $this->leftJoin(
            'products',
            'products.product_id',
            'product_childs.product_id')
            ->select(
                'product_childs.product_child_id as product_child_id',
                'product_childs.product_child_name as product_child_name',
                'product_childs.product_code as product_code'
            )
            ->where('products.is_deleted', self::IS_NOT_DELETED)
            ->where('product_childs.is_deleted', self::IS_NOT_DELETED);
        if (isset($filter['keyword'])) {
            $select->where(function ($query) use ($filter) {
                $query->where("product_childs.product_child_name",
                    "LIKE", "%" . $filter["keyword"] . "%")
                    ->orWhere("product_childs.product_code",
                        "LIKE", "%" . $filter["keyword"] . "%");
            });
        }
        return $select->paginate($filter['perpage'], $columns = ['*'], $pageName = 'page', $filter['page']);
    }

    /**
     * Inventory output
     * Danh sách option của product child load more theo trang
     * @param $filter
     *
     * @return mixed
     */
    public function getProductChildInventoryOutputOptionPage($filter)
    {
        $select = $this->leftJoin('products', 'products.product_id', '=', 'product_childs.product_id')
            ->select(
                'product_childs.product_child_id as product_child_id',
                'product_childs.product_code as product_code',
                'product_childs.product_child_name as product_child_name')
//            ->leftJoin('product_inventorys', 'product_inventorys.product_code', '=', 'product_childs.product_code')
//            ->where('product_inventorys.warehouse_id', $warehouseId)
            ->where('products.is_deleted', self::IS_NOT_DELETED)
            ->where('product_childs.is_deleted', self::IS_NOT_DELETED)
            ->where("products.is_actived", self::IS_ACTIVE)
            ->where("product_childs.is_actived", self::IS_ACTIVE);
        if (isset($filter['keyword'])) {
            $select->where(function ($query) use ($filter) {
                $query->where("product_childs.product_child_name",
                    "LIKE", "%" . $filter["keyword"] . "%")
                    ->orWhere("product_childs.product_code",
                        "LIKE", "%" . $filter["keyword"] . "%");
            });
        }
        return $select->paginate($filter['perpage'], $columns = ['*'], $pageName = 'page', $filter['page']);
    }

    /**
     * Lấy thông tin sản phẩm khuyến mãi
     *
     * @param $productCode
     * @return mixed
     */
    public function getProductPromotion($productCode)
    {
        return $this
            ->select(
                "product_child_id",
                "product_code",
                "product_child_name",
                "cost as old_price",
                "price as new_price"
            )
            ->where("product_code", $productCode)
            ->first();
    }


    public function getListProductChildCode($productId){
        return $this
            ->where('product_id',$productId)
            ->get();
    }
}
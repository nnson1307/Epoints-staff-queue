<?php
/**
 * Created by PhpStorm.
 * User: Mr Son
 * Date: 10/9/2018
 * Time: 4:24 PM
 */

namespace Modules\Survey\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use MyCore\Models\Traits\ListTableServiceTrait;
use MyCore\Models\Traits\ListTableTrait;


class ServiceTable extends Model
{
    use ListTableTrait;
    protected $connection = BRAND_CONNECTION;
    protected $table = 'services';
    protected $primaryKey = 'service_id';
    protected $fillable = [
        'service_id', 'service_name', 'service_category_id', 'service_code', 'is_sale', 'price_standard', 'service_type',
        'time', 'have_material', 'description', 'created_by', 'updated_by', 'created_at', 'updated_at',
        'is_actived', 'detail_description', 'service_avatar', 'slug', 'type_refer_commission', 'refer_commission_value',
        'type_staff_commission', 'staff_commission_value', 'type_deal_commission', 'deal_commission_value','is_surcharge',
        'is_remind', 'remind_value'
    ];


    /**
     * Build query table
     * @return mixed
     */
    protected function _getList($filter = [])
    {
        $ds = $this
            ->leftJoin('service_categories as cate', 'cate.service_category_id', '=', 'services.service_category_id')
            ->leftJoin('service_materials as mate', function ($join) {
                $join->on('mate.service_id', '=', 'services.service_id')
                    ->where('mate.is_deleted', 0);
            })
            ->select
            (
                'services.service_id as service_id',
                'services.service_avatar as service_avatar',
                'services.service_name as service_name',
                'services.service_code as service_code',
                'services.time as time',
                'services.service_category_id as category_id',
                'services.is_actived as is_actived',
                'services.created_at as created_at',
                'services.updated_at as updated_at',
                'mate.service_id as service_id_mate',
                'cate.name as name',
                'services.price_standard',
                \DB::raw('IF(mate.is_deleted = "0", COUNT(mate.service_material_id), "0") as  number')
            )
            ->where('services.is_deleted', 0)
            ->groupBy('services.service_id')
            ->orderBy('services.service_id', 'desc');
        if (isset($filter["created_at"]) && $filter["created_at"] != "") {
            $arr_filter = explode(" - ", $filter["created_at"]);
            $startTime = Carbon::createFromFormat('d/m/Y', $arr_filter[0])->format('Y-m-d');
            $endTime = Carbon::createFromFormat('d/m/Y', $arr_filter[1])->format('Y-m-d');
            $ds->whereBetween('services.created_at', [$startTime, $endTime]);
        }
        if (isset($filter['search']) != '') {
            $search = $filter['search'];
            $ds->where(function ($query) use ($search) {
                $query->where('services.service_name', 'like', '%' . $search . '%')
                    ->orWhere('services.service_code', 'like', '%' . $search . '%')
                    ->where('services.is_deleted', 0);
            });

        }
        return $ds;
    }

    protected function _getListPriceService(&$filter = [])
    {
        $ds = $this->leftJoin('service_categories as cate', 'cate.service_category_id', '=', 'services.service_category_id')
            ->leftJoin('service_branch_prices as sv_branch', 'sv_branch.service_id', '=', 'services.service_id')
            ->select('services.service_id as service_id',
                'services.service_name as service_name',
                'services.service_code as service_code',
                'services.time as time',
                'services.service_category_id as category_id',
                'services.is_actived as is_actived',
                'services.created_at as created_at',
                'services.updated_at as updated_at',
                'cate.name as name',
                'sv_branch.new_price as new_price',
                DB::raw("COUNT(services.service_id) as number"))
            ->where('services.is_deleted', 0)
            ->groupBy('services.service_id');
//        if(isset($filter["created_at"]) != "") {
//            $arr_filter = explode(" - ", $filter["created_at"]);
//            $from = Carbon::createFromFormat('d/m/Y', $arr_filter[0])->format('Y-m-d');
//            $ds->whereDate('services.created_at', $from);
//        }
//        unset($filter["created_at"]);
//
//        if (isset($filter['search']) != '') {
//            $search=$filter['search'];
//            $ds->where('services.service_name','like','%'.$search.'%')
//                ->orWhere('services.service_code','like','%'.$search.'%')->where('services.is_deleted',0);
//        }
//        unset($filter['search']);
        return $ds;
    }

    /**
     * Search and filter list price service
     * @param array $filter
     * @return mixed
     */
    public function getListPriceService(array $filter = [])
    {
        $select = $this->_getListPriceService($filter);
        $page = (int)($filter['page'] ?? 1);
        $display = (int)($filter['display'] ?? PAGING_ITEM_PER_PAGE);
        // search term
        if (!empty($filter['search_type']) && !empty($filter['search_keyword'])) {
            $select->where($filter['search_type'], 'like', '%' . $filter['search_keyword'] . '%');
        }
        unset($filter['search_type'], $filter['search_keyword'], $filter['page'], $filter['display']);

        // filter list
        foreach ($filter as $key => $val) {
            if (trim($val) == '') {
                continue;
            }

            $select->where(str_replace('$', '.', $key), $val);
        }

        return $select->paginate($display, $columns = ['*'], $pageName = 'page', $page);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function add(array $data)
    {
        $add = $this->create($data);
        return $add->service_id;
    }


    /**
     * @param $id
     */
    public function remove($id)
    {
        $this->where('services.service_id', $id)->update(['is_deleted' => 1]);
    }


    /**
     * Build query table
     * @param $id
     * @return mixed
     */
    public function getItem($id)
    {

        $ds = $this
            ->leftJoin('service_categories', 'service_categories.service_category_id', '=', 'services.service_category_id')
//            ->leftJoin('service_materials', 'service_materials.service_id', '=', 'services.service_id')
            ->select('services.service_id as service_id',
                'services.service_name as service_name',
                'services.service_category_id as service_category_id',
                'services.service_code as service_code',
                'services.is_sale as is_sale',
                'services.price_standard as price_standard',
                'services.service_type as service_type',
                'services.time as time',
                'services.have_material as have_material',
                'services.description as description',
                'services.detail_description as detail_description',
                'service_categories.name as name',
                'services.created_by as created_by',
                'services.updated_by as updated_by ',
                'services.created_at as created_at',
                'services.updated_at as updated_at',
                'services.is_actived as is_actived',
                'services.service_avatar as service_avatar',
                'services.is_sale as sale',
                'services.type_refer_commission',
                'services.refer_commission_value',
                'services.type_staff_commission',
                'services.staff_commission_value',
                'services.type_deal_commission',
                'services.deal_commission_value',
                'services.is_surcharge',
//                'service_materials.material_id as mate_id'
//                'service_materials.material_detail as material_detail',
                "{$this->table}.is_remind",
                "{$this->table}.remind_value"
            )->where('services.service_id', $id)
//            ->where('service_materials.is_deleted', 0)
            ->first();
        return $ds;
    }


    /**
     * @param array $data
     * @param $id
     * @return mixed
     */
    public function edit(array $data, $id)
    {
        return $this->where('services.service_id', $id)->update($data);
    }


    /**
     * @param $name
     * @param $id
     * @return mixed
     */
    public function testName($name, $id)
    {
        return $this->where('slug', $name)
            ->where('service_id', '<>', $id)->where('is_deleted', 0)->first();
    }

    /**
     * @return mixed
     */
    public function getServiceOption()
    {
        return $this->select('service_id', 'service_name', 'service_code', 'price_standard', 'time')
            ->where('is_deleted', 0)->where('is_actived', 1)->get()->toArray();
    }


    /**
     * @param $data
     * @return mixed
     */
    public function getServiceSearch($data)
    {
        $select = $this
            ->select('service_id', 'service_name', 'service_code', 'time', 'price_standard', 'service_avatar')
            ->where('service_name', 'like', '%' . $data . '%')
            ->where('is_deleted', 0)->get();
        unset($data);
        return $select;
    }

    /**
     * @param array $filter
     * @return mixed
     */
    public function getAll($filter = [])
    {
        $ds = $this
            ->leftJoin('service_categories as cate', 'cate.service_category_id', '=', 'services.service_category_id')
            ->leftJoin('service_materials as mate', 'mate.service_id', '=', 'services.service_id')
            ->select('services.service_id as service_id', 'services.service_name as service_name', 'services.service_code as service_code', 'services.time as time', 'services.service_category_id as category_id',
                'services.is_actived as is_actived', 'services.created_at as created_at', 'services.updated_at as updated_at',
                'mate.service_id as service_id_mate', 'cate.name as name',
                DB::raw("COUNT(services.service_id) as number"))
            ->where('services.is_deleted', 0)
            ->where("services.is_actived", 1)
            ->groupBy('services.service_id');


        if (isset($filter["service_type"]) && $filter["service_type"] != "") {
            $ds->where("services.service_category_id", $filter["service_type"]);
        }

        if (isset($filter["keyword"]) && $filter["keyword"] != "") {
            $ds->where("services.service_name", "LIKE", "%" . $filter["keyword"] . "%");
//                ->orWhere("services.service_code","LIKE","%".$filter["keyword"]."%");
        }


        return $ds->get();
    }

    /**
     * @param $arr_id
     * @return mixed
     */
    public function getServiceInId($arr_id)
    {
        $ds = $this
            ->leftJoin('service_categories as cate', 'cate.service_category_id', '=', 'services.service_category_id')
            ->leftJoin('service_materials as mate', 'mate.service_id', '=', 'services.service_id')
            ->select('services.service_id as service_id', 'services.service_name as service_name', 'services.service_code as service_code', 'services.time as time', 'services.service_category_id as category_id',
                'services.is_actived as is_actived', 'services.created_at as created_at', 'services.updated_at as updated_at',
                'mate.service_id as service_id_mate', 'cate.name as name',
                DB::raw("COUNT(services.service_id) as number"))
            ->where('services.is_deleted', 0)
            ->where("services.is_actived", 1)
            ->whereIn("services.service_id", $arr_id)
            ->groupBy('services.service_id');
        return $ds->get();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getItemImage($id)
    {
        $ds = $this->leftJoin('service_images', 'service_images.service_id', '=', 'services.service_id')
            ->select('service_images.name as image')
            ->where('services.service_id', $id)->get();
        return $ds;
    }

    /**
     *
     */
    public function getListAdd()
    {
        $ds = $this->select('service_id',
            'service_name',
            'price_standard',
            'time',
            'service_avatar',
            'service_code')->where('is_deleted', 0)->get();
        return $ds;
    }

    public function getService($name, $serviceCategory)
    {
        $ds = $this->leftJoin('service_categories', 'service_categories.service_category_id', '=', 'services.service_category_id')
            ->selectRaw('services.service_id as service_id,
                        services.service_name as service_name,
                        services.service_category_id as service_category_id,
                        services.price_standard as price_standard,
                        service_categories.name as service_category_name')
            ->where('services.is_deleted', 0);
        if ($name != null) {
            $ds->where('services.service_name', 'LIKE', '%' . $name . '%');
        }
        if ($serviceCategory != null) {
            $ds->where('services.service_category_id', $serviceCategory);
        }
        return $ds->get();
    }

    /**
     * Lấy dịch vụ theo code
     *
     * @param $id
     * @return mixed
     */
    public function getItemByCode($code)
    {
        $item = $this
            ->leftJoin('service_categories', 'service_categories.service_category_id', '=', 'services.service_category_id')
            ->select('services.service_id as service_id',
                'services.service_name as service_name',
                'services.service_category_id as service_category_id',
                'services.service_code as service_code',
                'services.is_sale as is_sale',
                'services.price_standard as price_standard',
                'services.service_type as service_type'
            )->where('services.service_code', $code)
            ->first();
        return $item;
    }

    /**
     * Lấy thông tin dịch vụ khuyến mãi
     *
     * @param $serviceCode
     * @return mixed
     */
    public function getServicePromotion($serviceCode)
    {
        return $this
            ->select(
                "service_id",
                "service_name",
                "service_code",
                "price_standard as new_price"
            )
            ->where("service_code", $serviceCode)
            ->first();
    }
}
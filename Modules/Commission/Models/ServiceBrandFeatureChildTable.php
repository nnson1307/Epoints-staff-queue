<?php
/**
 * Created by PhpStorm.
 * User: nguyenngocson
 * Date: 07/09/2022
 * Time: 14:08
 */

namespace Modules\Commission\Models;


use Illuminate\Database\Eloquent\Model;

class ServiceBrandFeatureChildTable extends Model
{
    protected $table      = "admin_service_brand_feature_child";
    protected $primaryKey = "service_brand_feature_child_id";

    /**
     * Lấy quyền của brand
     *
     * @param $brandId
     * @param $route
     * @return mixed
     */
    public function getServiceBrand($brandId, $route)
    {
        return $this
            ->where("brand_id", $brandId)
            ->where("feature_code", $route)
            ->groupBy("feature_code")
            ->first();
    }
}
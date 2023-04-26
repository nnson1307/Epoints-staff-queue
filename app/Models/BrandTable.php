<?php
/**
 * Created by PhpStorm
 * User: Mr Son
 * Date: 4/29/2020
 * Time: 11:29 AM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class BrandTable extends Model
{
    protected $table = "brand";
    protected $primaryKey = "brand_id";

    const IS_ACTIVE = 1;
    const NOT_DELETE = 0;

    /**
     * Lấy thông tin brand
     *
     * @param $brandCode
     * @return mixed
     */
    public function getAllBrand()
    {
        return $this
            ->select(
                "brand_id",
                "parent_id",
                "tenant_id",
                "brand_name",
                "brand_code",
                "brand_url",
                "brand_avatar",
                "brand_banner",
                "brand_about",
                "brand_contr",
                "company_name",
                "company_code",
                "position",
                "display_name"
            )
            ->where("is_activated", self::IS_ACTIVE)
            ->where("is_deleted", self::NOT_DELETE)
            ->get();
    }

    public function getBrandByTenant($tenantId){
        return $this
            ->where("tenant_id", $tenantId)
            ->where("is_activated", self::IS_ACTIVE)
            ->where("is_deleted", self::NOT_DELETE)
            ->first();
    }
}

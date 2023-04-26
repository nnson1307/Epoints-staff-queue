<?php
/**
 * Created by PhpStorm.
 * User: nguyenngocson
 * Date: 30/07/2021
 * Time: 14:47
 */

namespace Modules\ManageWork\Models;


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
     * @param $isSample
     * @return mixed
     */
    public function getAllBrand($isSample = 0)
    {
        $ds = $this
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
            ->where("is_deleted", self::NOT_DELETE);

        if ($isSample == 0) {
            $ds->whereNotIn("brand_code", ['sample']);
        } else {
            $ds->whereIn("brand_code", ['sample']);
        }

        return $ds->get();
    }

    /**
     * Lấy thông tin brand bằng brand_code
     *
     * @param $brandCode
     * @return mixed
     */
    public function getBrandByCode($brandCode)
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
            ->where("brand_code", $brandCode)
            ->where("is_activated", self::IS_ACTIVE)
            ->where("is_deleted", self::NOT_DELETE)
            ->first();
    }

    /**
     * Lấy thông tin brand bằng idTenant
     *
     * @param $idTenant
     * @return mixed
     */
    public function getBrandByTenant($idTenant)
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
            ->where("tenant_id", $idTenant)
            ->where("is_activated", self::IS_ACTIVE)
            ->where("is_deleted", self::NOT_DELETE)
            ->first();
    }
}
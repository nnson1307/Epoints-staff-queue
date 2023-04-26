<?php
/**
 * Created by PhpStorm
 * User: Mr Son
 * Date: 1/14/2021
 * Time: 11:50 AM
 */

namespace Modules\Contract\Models;


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
}
<?php
/**
 * Created by PhpStorm
 * User: Mr Son
 * Date: 4/29/2020
 * Time: 11:29 AM
 */

namespace App\Models\Brand;


use Illuminate\Database\Eloquent\Model;

class ConfigTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = "config";
    protected $primaryKey = "config_id";

    const IS_ACTIVE = 1;
    const NOT_DELETE = 0;

    /**
     * Lấy thông tin brand
     *
     * @param $brandCode
     * @return mixed
     */
    public function getAll()
    {
        return $this
            //->where("is_show", self::IS_ACTIVE)
            ->get()->pluck('value', 'key');
    }
}

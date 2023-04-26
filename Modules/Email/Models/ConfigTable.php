<?php
/**
 * Created by PhpStorm.
 * User: nguyenngocson
 * Date: 30/07/2021
 * Time: 14:47
 */

namespace Modules\Email\Models;


use Illuminate\Database\Eloquent\Model;

class ConfigTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = "config";
    protected $primaryKey = "config_id";
    public const UPDATED_AT = null;


    /**
     * Lấy giá trị theo key
     * @param $data
     * @param $id
     * @return mixed
     */
    public function getByKey($key){
        return $this->where('key',$key)->first();
    }
}
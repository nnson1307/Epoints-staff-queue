<?php
/**
 * Created by PhpStorm.
 * User: nguyenngocson
 * Date: 25/11/2021
 * Time: 14:03
 */

namespace Modules\Contract\Models;


use Illuminate\Database\Eloquent\Model;

class ContractRemindMapMethodTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = "contract_category_remind_map_method";
    protected $primaryKey = "contract_category_remind_map_method_id";

    /**
     * Lấy hình thức gửi của cấu hình nhắc nhở
     *
     * @param $remindId
     * @return mixed
     */
    public function getMethod($remindId)
    {
        return $this->where("contract_category_remind_id", $remindId)->get();
    }
}
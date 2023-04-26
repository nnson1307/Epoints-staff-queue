<?php
/**
 * Created by PhpStorm.
 * User: nguyenngocson
 * Date: 25/11/2021
 * Time: 14:02
 */

namespace Modules\Contract\Models;


use Illuminate\Database\Eloquent\Model;

class ContractRemindMapReceiverTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = "contract_category_remind_map_receiver";
    protected $primaryKey = "contract_category_remind_map_receiver_id";

    /**
     * Lấy người nhận của cấu hình nhắc nhở
     *
     * @param $remindId
     * @return mixed
     */
    public function getReceiver($remindId)
    {
        return $this->where("contract_category_remind_id", $remindId)->get();
    }
}
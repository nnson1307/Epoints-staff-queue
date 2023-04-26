<?php
/**
 * Created by PhpStorm.
 * User: nguyenngocson
 * Date: 25/11/2021
 * Time: 14:37
 */

namespace Modules\Contract\Models;


use Illuminate\Database\Eloquent\Model;

class ContractSignMapTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = "contract_sign_map";
    protected $primaryKey = "contract_sign_id";

    /**
     * Lấy người ký HĐ
     *
     * @param $contractId
     * @return mixed
     */
    public function getSignMap($contractId)
    {
        return $this
            ->select(
                "{$this->table}.contract_sign_id",
                "{$this->table}.contract_id",
                "{$this->table}.sign_by",
                "sf.full_name as sign_name"
            )
            ->join("staffs as sf", "sf.staff_id", "=", "{$this->table}.sign_by")
            ->where("{$this->table}.contract_id", $contractId)
            ->get();
    }
}
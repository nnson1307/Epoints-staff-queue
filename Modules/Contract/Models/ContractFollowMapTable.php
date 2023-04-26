<?php
/**
 * Created by PhpStorm.
 * User: nguyenngocson
 * Date: 25/11/2021
 * Time: 14:53
 */

namespace Modules\Contract\Models;


use Illuminate\Database\Eloquent\Model;

class ContractFollowMapTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = "contract_follow_map";
    protected $primaryKey = "contract_follow_map_id";

    /**
     * Lấy người theo dõi HĐ
     *
     * @param $contractId
     * @return mixed
     */
    public function getFollowMap($contractId)
    {
        return $this
            ->select(
                "{$this->table}.contract_follow_map_id",
                "{$this->table}.contract_id",
                "{$this->table}.follow_by",
                "sf.full_name as follow_name"
            )
            ->join("staffs as sf", "sf.staff_id", "=", "{$this->table}.follow_by")
            ->where("{$this->table}.contract_id", $contractId)
            ->get();
    }

}
<?php
/**
 * Created by PhpStorm.
 * User: nguyenngocson
 * Date: 25/11/2021
 * Time: 16:04
 */

namespace Modules\Contract\Models;


use Illuminate\Database\Eloquent\Model;

class ContractTagMapTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = "contract_tag_map";
    protected $primaryKey = "contract_tag_map_id";

    const NOT_DELETED = 0;

    /**
     * Lấy tag HĐ
     *
     * @param $contractId
     * @return mixed
     */
    public function getContractTag($contractId)
    {
        return $this
            ->select(
                "{$this->table}.contract_tag_map_id",
                "{$this->table}.contract_id",
                "{$this->table}.tag_id",
                "tag.name as tag_name"
            )
            ->join("contract_tags as tag", "tag.contract_tag_id", "=", "{$this->table}.tag_id")
            ->where("{$this->table}.contract_id", $contractId)
            ->get();
    }
}
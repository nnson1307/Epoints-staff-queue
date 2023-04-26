<?php

namespace Modules\Commission\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionObjectMapTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = "commission_object_map";
    protected $primaryKey = "commission_object_map_id";

    /**
     * Lâý đối tượng áp dụng hàng hoá
     *
     * @param $idCommission
     * @return mixed
     */
    public function getObjectMap($idCommission)
    {
        return $this
            ->select(
                "{$this->table}.commission_object_map_id",
                "{$this->table}.commission_id",
                "{$this->table}.object_type",
                "{$this->table}.object_id"
            )
            ->where("commission_id", $idCommission)
            ->get();
    }
}
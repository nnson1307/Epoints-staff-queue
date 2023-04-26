<?php
/**
 * Created by PhpStorm
 * User: Mr Son
 * Date: 15-04-02020
 * Time: 11:18 AM
 */

namespace Modules\JobNotify\Models;


use Illuminate\Database\Eloquent\Model;

class ResetRankLogTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = "reset_rank_log";
    protected $primaryKey = "id";

    /**
     * Lịch sử reset hạng lần cuối
     *
     * @param $customerId
     * @return mixed
     */
    public function getLastResetRank($customerId)
    {
        return $this
            ->select(
                "$this->primaryKey",
                "{$this->table}.customer_id",
                "{$this->table}.member_level_id",
                "{$this->table}.member_level_old_id",
                "{$this->table}.time_reset_rank_id",
                "{$this->table}.month_reset",
                "old.name as rank_old_name",
                "old.point as point_old",
                "new.name as rank_new_name",
                "new.point as point_new",
                "{$this->table}.created_at"
            )
            ->join("member_levels as old", "old.member_level_id", "=", "{$this->table}.member_level_old_id")
            ->join("member_levels as new", "new.member_level_id", "=", "{$this->table}.member_level_id")
            ->where("{$this->table}.customer_id", $customerId)
            ->latest("$this->primaryKey")
            ->first();
    }
}
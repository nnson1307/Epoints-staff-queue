<?php
/**
 * Created by PhpStorm.
 * User: nguyenngocson
 * Date: 24/11/2021
 * Time: 16:38
 */

namespace Modules\Contract\Models;


use Illuminate\Database\Eloquent\Model;

class ContractExpectedRevenueLogTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = "contract_expected_revenue_log";
    protected $primaryKey = "contract_expected_revenue_log_id";

    /**
     * Lấy log ngày gửi cố định, tuỳ chọn ngày
     *
     * @param $expectedRevenueId
     * @return mixed
     */
    public function getLog($expectedRevenueId)
    {
        return $this
            ->select(
                "contract_expected_revenue_log_id",
                "contract_expected_revenue_id",
                "date_send"
            )
            ->where("contract_expected_revenue_id", $expectedRevenueId)
            ->get();
    }
}
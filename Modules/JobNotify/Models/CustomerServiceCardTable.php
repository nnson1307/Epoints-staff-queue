<?php
/**
 * Created by PhpStorm
 * User: Mr Son
 * Date: 14-04-02020
 * Time: 3:24 PM
 */

namespace Modules\JobNotify\Models;


use Illuminate\Database\Eloquent\Model;

class CustomerServiceCardTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = "customer_service_cards";
    protected $primaryKey = "customer_service_card_id";

    /**
     * Lấy thông tin thẻ dịch vụ của khách hàng
     *
     * @param $customerServiceCardId
     * @return mixed
     */
    public function getInfo($customerServiceCardId)
    {
        return $this
            ->select(
                "service_cards.name as service_card_name",
                "{$this->table}.card_code",
                "{$this->table}.expired_date",
                "{$this->table}.number_using",
                "{$this->table}.count_using",
                "{$this->table}.created_at"
            )
            ->join("service_cards", "service_cards.service_card_id", "=", "{$this->table}.service_card_id")
            ->where("{$this->table}.customer_service_card_id", $customerServiceCardId)
            ->first();
    }
}
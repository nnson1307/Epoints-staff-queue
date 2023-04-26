<?php

namespace Modules\Survey\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceCardTable extends Model
{   
    protected $connection = BRAND_CONNECTION;
    protected $table = "service_cards";
    protected $primaryKey = "service_card_id";
    protected $fillable = [
        'service_card_id',
        'service_card_group_id',
        'name',
        'code',
        'service_is_all',
        'service_id',
        'service_card_type',
        'date_using',
        'number_using',
        'price',
        'money',
        'image',
        'is_actived',
        'is_deleted',
        'updated_by',
        'created_by',
        'created_at',
        'updated_at',
        'description',
        'slug',
        'type_refer_commission',
        'refer_commission_value',
        'type_staff_commission',
        'staff_commission_value',
        'type_deal_commission',
        'deal_commission_value',
        'is_surcharge'
    ];
    const IS_ACTIVED = 1;
    const IS_DELETED = 0;

    public function getOption()
    {
        $select = $this->select(
            "service_card_id",
            "name"
        )
            ->where("is_actived", self::IS_ACTIVED)
            ->where("is_deleted", self::IS_DELETED);
        return $select->get();
    }
    public function getAll()
    {
        $data = $this->select("service_card_id", "name")
            ->where('is_deleted', 0)
            ->where("is_actived", 1)
            ->where("is_surcharge", 0);
        return $data->get()->toArray();
    }
}

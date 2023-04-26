<?php

namespace Modules\Commission\Models;

use Illuminate\Database\Eloquent\Model;

class StaffCommissionEveryDayTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = "staff_commission_every_day";
    protected $primaryKey = "staff_commission_every_day_id";
    protected $fillable = [
        "staff_commission_every_day_id",
        "commission_id",
        "staff_id",
        "commission_money",
        "date",
        "day",
        "week",
        "month",
        "year",
        "created_at",
        "updated_at"
    ];
}
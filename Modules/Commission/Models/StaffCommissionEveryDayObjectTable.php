<?php

namespace Modules\Commission\Models;

use Illuminate\Database\Eloquent\Model;

class StaffCommissionEveryDayObjectTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = "staff_commission_every_day_object";
    protected $primaryKey = "staff_commission_every_day_object_id";
}
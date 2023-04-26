<?php
/**
 * Created by PhpStorm
 * User: Mr Son
 * Date: 14-04-02020
 * Time: 3:02 PM
 */

namespace Modules\JobNotify\Models;


use Illuminate\Database\Eloquent\Model;

class CustomerAppointmentTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = "customer_appointments";
    protected $primaryKey = "customer_appointment_id";

    /**
     * Lấy thông tin lịch hẹn
     *
     * @param $customerAppointmentId
     * @return mixed
     */
    public function getInfo($customerAppointmentId)
    {
        return $this
            ->select(
                "branches.branch_name",
                "{$this->table}.customer_appointment_id",
                "{$this->table}.customer_appointment_code",
                "{$this->table}.customer_appointment_type",
                "{$this->table}.date",
                "{$this->table}.time",
                "{$this->table}.status"
            )
            ->join("branches", "branches.branch_id", "=", "{$this->table}.branch_id")
            ->where("{$this->table}.customer_appointment_id", $customerAppointmentId)
            ->first();
    }
}
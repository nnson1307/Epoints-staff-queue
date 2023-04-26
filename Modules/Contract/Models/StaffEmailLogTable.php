<?php
/**
 * Created by PhpStorm.
 * User: nguyenngocson
 * Date: 25/11/2021
 * Time: 17:24
 */

namespace Modules\Contract\Models;


use Illuminate\Database\Eloquent\Model;

class StaffEmailLogTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = "staff_email_log";
    protected $primaryKey = "staff_email_log_id";
    protected $fillable = [
        "staff_email_log_id",
        "email_type",
        "email_subject",
        "email_subject",
        "email_from",
        "email_to",
        "email_cc",
        "email_params",
        "is_error",
        "error_description",
        "is_run",
        "run_at",
        "created_at",
    ];

    public $timestamps = false;

    /**
     * Thêm email log
     *
     * @param $data
     * @return mixed
     */
    public function add($data)
    {
        return $this->create($data)->staff_email_log_id;
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: nguyenngocson
 * Date: 30/07/2021
 * Time: 14:47
 */

namespace Modules\Email\Models;


use Illuminate\Database\Eloquent\Model;

class StaffEmailLogTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = "staff_email_log";
    protected $primaryKey = "staff_email_log_id";
    public const UPDATED_AT = null;
    /**
     * lấy danh sách email chưa gửi
     */
    public function getListEmailSend(){
        return $this->where('is_run',0)->get();
    }

    /**
     * Cập nhật email
     * @param $data
     * @param $id
     * @return mixed
     */
    public function editEmail($id,$data){
        return $this->where('staff_email_log_id',$id)->update($data);
    }
}